<?php

namespace App\Jobs;

use App\Mail\CsvSender;
use App\Models\File;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use App\Models\Number;
use Mail;
use Storage;

class ProcessChunk implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public array $chunk,
        public int $fileId,
        public int $currentChunk,
        public int $totalChunks,
        public int $userId,
        public int $numbersProcessed,
        public string $path
    ) {
    }

    public function handle(): void
    {
        try {
            $user = User::findOrFail($this->userId);
            $file = File::findOrFail($this->fileId);

            $client = new Client(['headers' => ['x-api-key' => env('PHONE_API_KEY')]]);
            $response = $client->post(env('PHONE_API_URL'), ['json' => $this->chunk]);
            $data = json_decode($response->getBody()->getContents(), true);

            error_log("Chunk procesado: " . count($data) . " registros");

            $numbersToUpdate = [];
            foreach ($data as $details) {
                Log::info("Número procesado: " . $details['issued']);
                $numbersToUpdate[] = [
                    'issued' => $details['issued'],
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
                ];
            }

            // Dividir en lotes de 1000 registros
            $batchSize = 1000;
            foreach (array_chunk($numbersToUpdate, $batchSize) as $batch) {
                // update them and then link them to the file
                Number::upsert($batch, ['issued'], [
                    'originalOperator',
                    'originalOperatorRaw',
                    'currentOperator',
                    'currentOperatorRaw',
                    'number',
                    'prefix',
                    'type',
                    'typeDescription',
                    'queriesLeft',
                    'lastPortabilityWhen',
                    'lastPortabilityFrom',
                    'lastPortabilityFromRaw',
                    'lastPortabilityTo',
                    'lastPortabilityToRaw',
                ]);

                $file->numbers()->attach(Number::whereIn('issued', array_column($batch, 'issued'))->get());
            }

            if ($this->currentChunk === $this->totalChunks) {
                $file->update(['processed' => true]);
                $downloadPath = "download/{$file->id}";
                Mail::to($user->email)->send(new CsvSender($downloadPath, $user->id, $this->numbersProcessed));
                Storage::disk('public')->delete($this->path);
            }

        } catch (\Throwable $th) {
            Log::error("Error al procesar el chunk: " . $th->getMessage(), ['exception' => $th]);
        }
    }

}
