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
            $path = storage_path('app/public/' . $user->name . '/csv');
            if (!is_dir($path)) {
                return;
            }
            // Sacamos los directorios de dentro del path
            $directories = array_diff(scandir($path), ['.', '..']);
            foreach ($directories as $directory) {
                $files = array_diff(scandir($path . '/' . $directory), ['.', '..']);
                foreach ($files as $file) {
                    $this->files[] = $directory . '/' . $file;
                }
            }
            // Le damos la vuelta al array para que los últimos archivos sean los primeros
            $this->files = array_reverse($this->files);
        }
    }

    #[Title('Administrar usuarios')]
    public function render()
    {
        return view('livewire.views.manage-users');
    }
}
