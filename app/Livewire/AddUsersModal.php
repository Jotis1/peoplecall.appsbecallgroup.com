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

    #[Validate('required')]
    public $name = '';
    #[Validate('numeric')]
    public $requests = '';

    public function rules()
    {
        return [
            'name' => 'required',
            'requests' => 'numeric',
        ];
    }

    public function messages(){
        return [
            'name.required' => 'El campo nombre es requerido',
            'requests.numeric' => 'El campo solicitudes debe ser un número',
        ];
    }

    public function save(){
        $validated = $this->validate();
        $validatedName = $validated['name'];
        $validatedRequests= $validated['requests'];
        
        $user = new User();
        $user->name = $validatedName;
        $user->password = "__init__";
        $user->email = "__init__";
        $user->monthly_requests = $validatedRequests ?? 0;
        $user->is_admin = False;
        $user->save();

        return $this->redirectRoute('manage-users');
    }

    public function render()
    {
        return view('livewire.add-users-modal');
    }
}
