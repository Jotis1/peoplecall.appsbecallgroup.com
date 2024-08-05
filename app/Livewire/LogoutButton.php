<?php

namespace App\Livewire;

use Livewire\Component;

class LogoutButton extends Component
{

    public function logout(){
        auth()->logout();
        return $this->redirectRoute('login');
    }

    public function render()
    {
        return view('livewire.logout-button');
    }
}
