<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ProcessingFiles extends Component
{
    public $files = [];

    public function mount()
    {
        $user = Auth::user();
        if (!$user)
            return;

        $this->files = $user->files()->where('processed', false)->get();
    }

    public function render()
    {
        return view('livewire.processing-files');
    }
}
