<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Validate;
use App\Models\IpWhitelist;

class EditIpsModal extends Component
{
    public $showModal = false;

    public function toggleModal()
    {
        $this->showModal = !$this->showModal;
    }

    public $id;
    #[Validate('ip')]
    public $ip;

    public function rules()
    {
        return [
            'ip' => 'required|ip',
        ];
    }

    public function messages()
    {
        return [
            'ip.required' => 'El campo IP es requerido',
            'ip.ip' => 'El campo IP debe ser una dirección IP válida',
        ];
    }

    public function save()
    {
        $validated = $this->validate();
        $ip = IpWhitelist::find($this->id);
        $ip->ip = $validated['ip'];
        $ip->save();
        return $this->redirectRoute('manage-ips');
    }

    public function render()
    {
        return view('livewire.edit-ips-modal');
    }
}
