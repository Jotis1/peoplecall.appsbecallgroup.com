<?php

namespace App\Exports;

use App\Mail\CsvSender;
use App\Models\File;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Excel;
use Mail;

class FilesExport implements FromQuery, ShouldQueue, WithHeadings, WithEvents
{
    use Exportable, RegistersEventListeners;

    private $writerType = Excel::CSV;

    public $user;

    public $file;

    public function __construct(public int $fileId)
    {
    }

    public function query()
    {
        $this->file = File::findOrFail($this->fileId);
        $this->user = User::findOrFail($this->file->user_id);

        return File::find($this->fileId)->numbers()->select([
            'issued',
            'currentOperator',
            'lastPortabilityWhen',
            'lastPortabilityFrom',
            'lastPortabilityTo',
        ]);
    }

    public function afterSheet($event)
    {
        // Aquí deberías enviar el correo o notificar al usuario que el archivo está listo.
        Mail::to($this->user->email)->send(new CsvSender($this->fileId, $this->user->id));

        $this->file->downloaded = true;
        $this->file->downloading = false;
        $this->file->save();
    }
    public function headings(): array
    {
        return [
            'Number',
            'Current Operator',
            'Last Portability When',
            'Last Portability From',
            'Last Portability To',
        ];
    }
}
