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
use Exception;

class ProcessCsvFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 0;
    public $tries = 5;
    public $user = null;

    public function __construct(public string $path, public int $fileId)
    {
        $this->path = $path;
        $this->fileId = $fileId;
    }

    public function handle(): void
    {
        try {
            $file = File::find($this->fileId);
            $user = User::find($file->user_id);
            $stream = Storage::disk('public')->readStream($this->path);

            $chunkSize = 10000;
            $chunk = 0;

            $numbers = [];
            $numberCount = 0;

            while (($data = fgets($stream)) !== false) {
                $data = str_replace(",", ";", $data);
                $data = preg_replace('/\s+/', '', $data);
                $firstRow = explode(";", $data)[0];
                $firstRow = str_replace("\xEF\xBB\xBF", "", $firstRow);
                $firstRow = preg_replace("/^(\+34|0034|34)/", "", $firstRow);

                if (empty($firstRow) || !is_numeric($firstRow) || strlen($firstRow) != 9) {
                    Log::info("Invalid phone number: $firstRow");
                    continue;
                }

                // find or create the number
                $number = Number::where('issued', $firstRow)->first();
                if (!$number) {
                    $number = new Number();
                    $number->issued = $firstRow;
                    $number->save();
                }

                $file->numbers()->attach($number);

                $numbers[] = $number->issued;
                $numberCount++;

                if ($numberCount === $chunkSize) {
                    $chunk++;
                    $this->processChunk($numbers);
                    $numbers = [];
                    $numberCount = 0;
                }
            }

            if ($numberCount > 0) {
                $this->processChunk($numbers);
            }

            $file->processed = true;
            $file->save();
            $user->executed_requests += $file->numbers()->count();
            $user->save();

            Storage::disk('public')->delete($this->path);

            $downloadPath = 'download/' . $file->id;
            Mail::to($user->email)->send(new CsvSender($downloadPath, $user->id, $file->numbers()->count()));
        } catch (\Throwable $th) {
            Log::error("Error al procesar el archivo");
            Log::error($th);
        }
    }

    public function processChunk(array $numbers)
    {
        try {
            $client = new Client([
                "headers" => [
                    "x-api-key" => env("PHONE_API_KEY")
                ]
            ]);
            $res = $client->post(env("PHONE_API_URL"), ["json" => $numbers]);
            $data = json_decode($res->getBody()->getContents(), true);

            foreach ($data as $response) {
                $number = Number::where('issued', $response["issued"])->first();
                $number->originalOperator = $response["originalOperator"] ?? null;
                $number->originalOperatorRaw = $response["originalOperatorRaw"] ?? null;
                $number->currentOperator = $response["currentOperator"] ?? null;
                $number->currentOperatorRaw = $response["currentOperatorRaw"] ?? null;
                $number->number = $response["number"] ?? null;
                $number->prefix = $response["prefix"] ?? null;
                $number->type = $response["type"] ?? null;
                $number->typeDescription = $response["typeDescription"] ?? null;
                $number->queriesLeft = $response["queriesLeft"] ?? null;
                $number->lastPortability = $response["lastPortability"]["when"] ?? null;
                $number->lastPortabilityWhen = $response["lastPortability"]["from"] ?? null;
                $number->lastPortabilityFrom = $response["lastPortability"]["fromRaw"] ?? null;
                $number->lastPortabilityTo = $response["lastPortability"]["to"] ?? null;
                $number->lastPortabilityToRaw = $response["lastPortability"]["toRaw"] ?? null;
                $number->save();
            }
        } catch (\Throwable $th) {
            Log::error("Error al procesar el chunk");
            Log::error($th);
        }
    }
}
