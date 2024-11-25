<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ProcessingFiles extends Component
{
    public $files = [];
    public function render()
    {
        $this->files = Auth::user()->files()->where('processed', false)->get();
        return view('livewire.processing-files', [
            'files' => $this->files
        ]);
    }
}
