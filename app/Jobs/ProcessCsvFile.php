<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use App\Models\User;
use App\Mail\CsvSender;
use App\Models\File;
use App\Models\Number;

class ProcessCsvFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 0;
    public $tries = 5;

    public function __construct(public string $path, public int $fileId)
    {
    }

    public function handle(): void
    {
        try {
            $file = File::findOrFail($this->fileId);
            $user = User::findOrFail($file->user_id);

            $stream = Storage::disk('public')->readStream($this->path);

            $chunkSize = 10000;
            $chunk = [];
            $numbersProcessed = 0;

            while (($data = fgets($stream)) !== false) {
                $cleanNumber = $this->sanitizePhoneNumber($data);

                if (!$cleanNumber) {
                    Log::info("Número inválido: $data");
                    continue;
                }

                $number = $this->getOrCreateNumber($cleanNumber);
                $file->numbers()->attach($number);
                $chunk[] = $number->issued;
                $numbersProcessed++;

                if (count($chunk) === $chunkSize) {
                    $this->processChunk($chunk);
                    $chunk = [];
                }
            }

            if (!empty($chunk)) {
                $this->processChunk($chunk);
            }

            $this->finalizeProcessing($file, $user, $numbersProcessed);

        } catch (\Throwable $th) {
            Log::error("Error al procesar el archivo: " . $th->getMessage(), ['exception' => $th]);
        }
    }

    private function sanitizePhoneNumber(string $data): ?string
    {
        $data = str_replace(",", ";", trim($data));
        $number = str_replace("\xEF\xBB\xBF", "", explode(";", $data)[0]);
        $number = preg_replace("/^(\+34|0034|34)/", "", $number);

        return (is_numeric($number) && strlen($number) === 9) ? $number : null;
    }

    private function getOrCreateNumber(string $issued): Number
    {
        return Number::firstOrCreate(['issued' => $issued]);
    }

    private function processChunk(array $numbers): void
    {
        try {
            $client = new Client(['headers' => ['x-api-key' => env('PHONE_API_KEY')]]);
            $response = $client->post(env('PHONE_API_URL'), ['json' => $numbers]);
            $data = json_decode($response->getBody()->getContents(), true);

            foreach ($data as $details) {
                $this->updateNumberDetails($details);
            }
        } catch (\Throwable $th) {
            Log::error("Error al procesar el chunk: " . $th->getMessage(), ['exception' => $th]);
        }
    }

    private function updateNumberDetails(array $details): void
    {
        $number = Number::where('issued', $details['issued'])->first();
        if ($number) {
            $number->fill([
                'originalOperator' => $details['originalOperator'] ?? null,
                'originalOperatorRaw' => $details['originalOperatorRaw'] ?? null,
                'currentOperator' => $details['currentOperator'] ?? null,
                'currentOperatorRaw' => $details['currentOperatorRaw'] ?? null,
                'number' => $details['number'] ?? null,
                'prefix' => $details['prefix'] ?? null,
                'type' => $details['type'] ?? null,
                'typeDescription' => $details['typeDescription'] ?? null,
                'queriesLeft' => $details['queriesLeft'] ?? null,
                'lastPortabilityWhen' => $details['lastPortability']['when'] ?? null,
                'lastPortabilityFrom' => $details['lastPortability']['from'] ?? null,
                'lastPortabilityFromRaw' => $details['lastPortability']['fromRaw'] ?? null,
                'lastPortabilityTo' => $details['lastPortability']['to'] ?? null,
                'lastPortabilityToRaw' => $details['lastPortability']['toRaw'] ?? null,
            ])->save();
        }
    }

    private function finalizeProcessing(File $file, User $user, int $numbersProcessed): void
    {
        $file->update(['processed' => true]);
        $user->increment('executed_requests', $numbersProcessed);

        Storage::disk('public')->delete($this->path);

        $downloadPath = "download/{$file->id}";
        Mail::to($user->email)->send(new CsvSender($downloadPath, $user->id, $numbersProcessed));
    }
}
