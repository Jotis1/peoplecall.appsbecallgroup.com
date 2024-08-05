<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attribtes\Validate;
use App\Models\User;

class EditUsersModal extends Component
{

    public $username = "Usuario";
    #[Validate("numeric")]
    public $requests;
    #[Validate("numeric")]
    public $user_id;
    public $showModal = false;

    public function toggleModal()
    {
        $this->showModal = !$this->showModal;
    }

    public function rules()
    {
        return [
            'requests' => 'required|numeric',
            'user_id' => 'required|numeric',
        ];
    }

    public function messages()
    {
        return [
            'requests.required' => 'El campo solicitudes es requerido',
            'requests.numeric' => 'El campo solicitudes debe ser un número',
            'user_id.required' => 'El campo usuario es requerido',
            'user_id.numeric' => 'El campo usuario debe ser un número',
        ];
    }

    public function save(){
        $validated = $this->validate();
        $id = $validated['user_id'];
        $requests = $validated['requests'];
        $user = User::find($id);
        $user->monthly_requests = $requests;
        $user->save();
        $this->redirectRoute('manage-users');
    }

    public function render()
    {
        return view('livewire.edit-users-modal');
    }
}
