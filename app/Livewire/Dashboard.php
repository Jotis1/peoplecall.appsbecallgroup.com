<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

class Dashboard extends Component
{
    #[Title('Panel de control')]
    public function render()
    {
        return view('livewire.views.dashboard');
    }

    public $isRunningJob = false;

    public function refreshIsRunningJob()
    {
        $this->isRunningJob = Auth::user()->is_running_job;
    }
}
