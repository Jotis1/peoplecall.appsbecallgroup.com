<?php

namespace App\Livewire;

use Livewire\Component;

class AdminButtons extends Component
{

    public $isOpen = false;

    public function toggleOptions(){
        $this->isOpen = !$this->isOpen;
    }

    public function render()
    {
        return view('livewire.admin-buttons');
    }
}
