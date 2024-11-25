<?php

namespace App\Jobs;

use App\Exports\FilesExport;
use App\Mail\CsvSender;
use App\Models\File;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Mail;

class ProcessDownload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    /**
     * Create a new job instance.
     */
    public function __construct(public int $fileId)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $file = File::findOrFail($this->fileId);
            $user = User::findOrFail($file->user_id);
            $file->downloaded = true;
            $file->downloading = false;
            $file->save();

            Mail::to($user->email)->send(new CsvSender($this->fileId, $user->id));
        } catch (\Throwable $th) {
            Log::error("Error al procesar el archivo para descarga");
            Log::error($th);
        }
    }
}
