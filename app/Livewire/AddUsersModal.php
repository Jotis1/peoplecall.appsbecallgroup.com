<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Validate;
use App\Models\User;

class AddUsersModal extends Component
{
    public $showModal = False;

    public function toggleModal()
    {
        $this->showModal = !$this->showModal;
    }


    #[Validate('required', message: 'El campo nombre es requerido')]
    public $name = '';
    #[Validate('numeric')]
    public $requests = '';
    public function save()
    {
        $this->validate();
        $user = new User();
        $user->name = $this->name;
        $user->password = "__init__";
        $user->email = "__init__";
        $user->monthly_requests = $this->requests ?: 0;
        $user->is_admin = False;
        $user->save();

        return $this->redirectRoute('manage-users');
    }

    public function render()
    {
        return view('livewire.add-users-modal');
    }
}
