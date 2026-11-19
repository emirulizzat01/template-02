<?php

namespace App\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class DialogExamples extends Component
{
    #[Locked]
    public string $confirmationResult = '';

    public string $label = '';

    public function resolveConfirmation(bool $confirmed): void
    {
        $this->confirmationResult = $confirmed ? 'Demo incident acknowledged.' : 'Action cancelled.';
    }

    public function submitLabel(): void
    {
        $this->label = trim($this->label);

        $validated = $this->validate(
            ['label' => ['required', 'string', 'min:2', 'max:40']],
            attributes: ['label' => 'service label'],
        );

        $this->dispatch('dialog-example-label-accepted', label: $validated['label'])->self();
        $this->resetLabel();
    }

    public function resetLabel(): void
    {
        $this->reset('label');
        $this->resetValidation('label');
    }

    public function render(): View
    {
        return view('livewire.dialog-examples');
    }
}
