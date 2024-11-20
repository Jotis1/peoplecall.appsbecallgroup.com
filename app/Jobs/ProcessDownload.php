<?php

namespace App\Jobs;

use App\Exports\FilesExport;
use App\Mail\CsvSender;
use App\Models\File;
use App\Jobs\ProcessDownloadChunk;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Mail;

class ProcessDownload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $fileId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $fileId)
    {
        $this->fileId = $fileId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $file = File::findOrFail($this->fileId);
            $user = User::findOrFail($file->user_id);
            $file->downloading = true;
            $file->save();

            // (new FilesExport($this->fileId))->store($file->name, 'local');
            Excel::store(new FilesExport($this->fileId), $file->name, 'local', \Maatwebsite\Excel\Excel::CSV);

            $file->downloaded = true;
            $file->downloading = false;
            $file->save();

            // Aquí deberías enviar el correo o notificar al usuario que el archivo está listo.
            Mail::to($user->email)->send(new CsvSender($file->name, $user->id));
        } catch (\Throwable $th) {
            Log::error("Error al procesar el archivo para descarga");
            Log::error($th);
        }
    }
}
