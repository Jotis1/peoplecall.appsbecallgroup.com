<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\IpWhitelist;

class ManageIps extends Component
{

    public function delete($id) {
        $ip = IpWhitelist::find($id);
        $ip->delete();
        $this->redirectRoute('manage-ips');
    }

    public function render()
    {
        return view('livewire.views.manage-ips')->title('Administrar IPs');
    }
}
