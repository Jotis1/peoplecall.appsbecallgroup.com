<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Validate;
use App\Models\IpWhitelist;

class AddIpsModal extends Component
{
    public $showModal = false;
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
        IpWhitelist::add($validated['ip']);
        $this->redirectRoute('manage-ips');
    }

    public function toggleModal()
    {
        $this->showModal = !$this->showModal;
    }

    public function render()
    {
        return view('livewire.add-ips-modal');
    }
}
