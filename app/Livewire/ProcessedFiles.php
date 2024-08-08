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
            foreach ($directories as $directory) {
                $files = array_diff(scandir($path . '/' . $directory), ['.', '..']);
                foreach ($files as $file) {
                    $this->files[] = $directory . '/' . $file;
                }
            }
            // Le damos la vuelta al array para que los últimos archivos sean los primeros
            $this->files = array_reverse($this->files);
        }
    }

    public function render()
    {
        return view('livewire.processed-files');
    }
}
