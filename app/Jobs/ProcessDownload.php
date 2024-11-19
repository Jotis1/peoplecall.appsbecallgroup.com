<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessDownload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public int $fileId)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $file = File::find($this->fileId);
            $file->downloading = true;
            $file->save();
            $filename = $file->name;
            $filePath = storage_path('app/public/' . $filename); // Ruta donde guardar el archivo
            $csvFile = fopen($filePath, 'w');

            // Escribir los encabezados del CSV
            $headers = ['issued', 'originalOperator', 'originalOperatorRaw', 'currentOperator', 'currentOperatorRaw', 'number', 'prefix', 'type', 'typeDescription', 'queriesLeft', 'lastPortabilityWhen', 'lastPortabilityFrom', 'lastPortabilityFromRaw', 'lastPortabilityTo', 'lastPortabilityToRaw'];
            fputcsv($csvFile, $headers);

            // Usar chunking para procesar los números en bloques
            $file->numbers()->chunk(1000, function ($numbers) use ($csvFile) {
                error_log("Procesando chunk de " . count($numbers) . " registros");
                foreach ($numbers as $number) {
                    fputcsv($csvFile, [
                        $number->issued,
                        $number->originalOperator,
                        $number->originalOperatorRaw,
                        $number->currentOperator,
                        $number->currentOperatorRaw,
                        $number->number,
                        $number->prefix,
                        $number->type,
                        $number->typeDescription,
                        $number->queriesLeft,
                        $number->lastPortabilityWhen,
                        $number->lastPortabilityFrom,
                        $number->lastPortabilityFromRaw,
                        $number->lastPortabilityTo,
                        $number->lastPortabilityToRaw,
                    ]);
                }
            });

            fclose($csvFile);

            // Puedes almacenar el archivo en algún lugar accesible (por ejemplo, public)
            Storage::disk('public')->put($filename, file_get_contents($filePath));
            $file->downloading = false;
        } catch (\Throwable $th) {
            Log::error("Error al procesar el archivo para descarga");
            Log::error($th);
        }
    }
}
