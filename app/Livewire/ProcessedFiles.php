<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ProcessedFiles extends Component
{
    public $files = [];
    public function render()
    {
        $this->files = Auth::user()->files()->where('processed', true)->get();
        return view('livewire.processed-files', [
            'files' => $this->files
        ]);
    }
}
