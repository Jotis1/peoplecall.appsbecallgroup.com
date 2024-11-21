<?php

namespace App\Exports;

use App\Models\File;

use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Excel;

class FilesExport implements FromQuery, ShouldQueue, WithHeadings
{
    use Exportable;

    private $writerType = Excel::CSV;

    public function __construct(public int $fileId)
    {
    }

    public function query()
    {
        // quiero q me devuelva los campos:
        // issued
        // currentOperator
        // last portability when
        // last portability from
        // last portability to

        return File::find($this->fileId)->numbers()->select([
            'issued',
            'currentOperator',
            'lastPortabilityWhen',
            'lastPortabilityFrom',
            'lastPortabilityTo',
        ]);
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
