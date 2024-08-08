<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\IpWhitelist;
use Livewire\Attributes\Title;

class ManageIps extends Component
{

    public function delete($id)
    {
        $ip = IpWhitelist::find($id);
        $ip->delete();
        $this->redirectRoute('manage-ips');
    }

    #[Title('Administrar IPs')]
    public function render()
    {
        return view('livewire.views.manage-ips');
    }
}
