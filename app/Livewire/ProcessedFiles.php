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
        if (!$user)
            return;

        $this->files = $user->files()->where('processed', true)->get();
    }

    public function render()
    {
        return view('livewire.processed-files');
    }
}
