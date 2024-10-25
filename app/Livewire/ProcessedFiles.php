<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ProcessedFiles extends Component
{
    public $files = [];

    public function mount()
    {
        $user = Auth::user();
        if ($user) {
            $path = storage_path('app/public/' . $user->name . '/csv');
            // Comprobamos si el directorio existe
            if (!is_dir($path)) {
                return;
            }

            // Sacamos los directorios de dentro del path
            $directories = array_diff(scandir($path), ['.', '..']);
            $filesWithTimestamps = [];

            foreach ($directories as $directory) {
                // Partimos el nombre del directorio (dd-mm-yyyy)
                [$day, $month, $year] = explode('-', $directory);

                // Sacamos los archivos de dentro de cada directorio
                $files = array_diff(scandir($path . '/' . $directory), ['.', '..']);
                foreach ($files as $file) {
                    // Partimos el nombre del archivo (hh-mm-ss)
                    [$hour, $minute, $second] = explode('-', pathinfo($file, PATHINFO_FILENAME));

                    // Convertimos la fecha y la hora en un timestamp para ordenar
                    $timestamp = mktime($hour, $minute, $second, $month, $day, $year);

                    // Guardamos el archivo junto con su timestamp
                    $filesWithTimestamps[] = [
                        'path' => $directory . '/' . $file,
                        'timestamp' => $timestamp,
                    ];
                }
            }

            // Ordenamos los archivos por timestamp de más reciente a más antiguo
            usort($filesWithTimestamps, function ($a, $b) {
                return $b['timestamp'] - $a['timestamp']; // Orden descendente
            });

            // Extraemos solo los paths ordenados
            $this->files = array_column($filesWithTimestamps, 'path');
        }
    }

    public function render()
    {
        return view('livewire.processed-files');
    }
}
