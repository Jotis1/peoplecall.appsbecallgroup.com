<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Title;

class ManageUsers extends Component
{

    public $currentUserId = null;

    public function delete($id)
    {
        $user = User::find($id);
        $user->delete();
        $this->redirectRoute('manage-users');
    }

    public $files = [];

    public function getFiles($id)
    {
        if ($this->currentUserId === $id) {
            $this->currentUserId = null;
            $this->files = [];
            return;
        }
        $this->currentUserId = $id;
        $user = User::find($id);
        if ($user) {
            // get only the processed files
            $this->files = $user->files()->where('processed', true)->get();
        }
    }

    #[Title('Administrar usuarios')]
    public function render()
    {
        return view('livewire.views.manage-users');
    }
}
