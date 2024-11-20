<?php

namespace App\Exports;

use App\Models\File;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;

class FilesExport implements FromQuery, WithHeadings
{
    use Exportable;

    public int $queryCount;

    public function __construct(public int $fileId)
    {
    }

    public function query()
    {
        $file = File::find($this->fileId);
        if (!$file) {
            Log::error("Archivo no encontrado para el ID: {$this->fileId}");
            return collect([]);
        }

        // we will return all the number associated with the file
        $query = $file->numbers();
        $this->queryCount = $query->count();
        return $query;
    }

    /**
     * Define los encabezados para la exportación.
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID',
            'Issued',
            'Original Operator',
            'Original Operator Raw',
            'Current Operator',
            'Current Operator Raw',
            'Number',
            'Prefix',
            'Type',
            'Type Description',
            'Queries Left',
            'Last Portability',
            'Last Portability When',
            'Last Portability From',
            'Last Portability From Raw',
            'Last Portability To',
            'Last Portability To Raw',
        ];
    }
}
