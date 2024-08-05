<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;

class ManageUsers extends Component
{

    public function delete($id) {
        $user = User::find($id);
        $user->delete();
        $this->redirectRoute('manage-users');
    }

    public function render()
    {
        return view('livewire.views.manage-users')->title('Administrar usuarios');
    }
}
