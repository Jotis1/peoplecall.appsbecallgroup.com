<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\File;

class ProcessCsvFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 0; // Permitir procesamiento largo
    public $tries = 5;

    public function __construct(public string $path, public int $fileId, public int $fileLines)
    {
    }

    public function handle(): void
    {
        try {
            $file = File::findOrFail($this->fileId);
            $user = User::findOrFail($file->user_id);

            $stream = Storage::disk('public')->readStream($this->path);
            $chunkSize = 10000; // Tamaño del chunk
            $chunk = [];
            $numbersProcessed = 0;
            $currentChunk = 0;
            $totalChunks = ceil($this->fileLines / $chunkSize);

            // Leer línea por línea
            while (($line = fgets($stream)) !== false) {
                $cleanNumber = $this->sanitizePhoneNumber($line);

                if (!$cleanNumber) {
                    Log::info("Número inválido: $line");
                    continue;
                }

                $chunk[] = $cleanNumber;
                $numbersProcessed++;

                if (count($chunk) === $chunkSize) {
                    $this->dispatchChunkJob($chunk, ++$currentChunk, $totalChunks, $user->id, $numbersProcessed);
                    $chunk = []; // Limpiar el chunk
                }
            }

            // Procesar cualquier chunk restante
            if (!empty($chunk)) {
                $this->dispatchChunkJob($chunk, ++$currentChunk, $totalChunks, $user->id, $numbersProcessed);
            }

            $this->finalizeProcessing($user, $numbersProcessed);

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

    private function dispatchChunkJob(array $chunk, int $currentChunk, int $totalChunks, int $userId, int $numbersProcessed): void
    {
        // Crea un nuevo job para procesar este chunk
        ProcessChunk::dispatch($chunk, $this->fileId, $currentChunk, $totalChunks, $userId, $numbersProcessed, $this->path);
    }

    private function finalizeProcessing(User $user, int $numbersProcessed): void
    {
        $user->increment('executed_requests', $numbersProcessed);
    }
}
