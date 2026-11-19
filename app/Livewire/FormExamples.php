<?php

namespace App\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.signal')]
class FormExamples extends Component
{
    #[Locked]
    public ?string $example = null;

    #[Locked]
    public bool $submitted = false;

    public array $values = [];

    public string $promptValue = '';

    public function mount(bool $dedicated = false): void
    {
        if ($dedicated) {
            $this->example = 'dedicated';
        }
    }

    public function openExample(string $example): void
    {
        abort_unless($this->example !== 'dedicated' && in_array($example, ['modal', 'drawer'], true), 404);

        $this->resetForm();
        $this->example = $example;
    }

    public function closeExample(): void
    {
        if ($this->example !== 'dedicated') {
            $this->example = null;
            $this->resetForm();
        }
    }

    public function fillSample(): void
    {
        $this->resetForm();
        $this->values = array_map(fn (array $field) => $field['sample'], $this->fields());
    }

    public function resetForm(): void
    {
        $this->reset('values', 'submitted', 'promptValue');
        $this->resetValidation();
    }

    public function requestDiscard(): void
    {
        $hasValues = collect($this->values)->contains(fn (mixed $value): bool => filled($value));

        if ($hasValues && ($this->example === 'dedicated' || ! $this->submitted)) {
            $this->dispatch('example-discard-requested')->self();

            return;
        }

        $this->discard();
    }

    public function discard(): void
    {
        if ($this->example === 'dedicated') {
            $this->resetForm();

            return;
        }

        $this->dispatch('example-close-requested')->self();
    }

    public function openTitlePrompt(): void
    {
        abort_if($this->example === null, 404);

        $this->resetValidation('promptValue');
        $this->promptValue = $this->values[$this->promptFieldName()] ?? '';
        $this->dispatch('example-prompt-opened')->self();
    }

    public function applyTitlePrompt(): void
    {
        abort_if($this->example === null, 404);

        $name = $this->promptFieldName();
        $field = $this->fields()[$name];
        $this->promptValue = trim($this->promptValue);
        $validated = $this->validateOnly('promptValue', ['promptValue' => $field['rules']], attributes: [
            'promptValue' => strtolower($field['label']),
        ]);

        if ($validated['promptValue'] !== ($this->values[$name] ?? '')) {
            $this->values[$name] = $validated['promptValue'];
            $this->submitted = false;
        }

        $this->resetValidation('values.'.$name);
        $this->dispatch('example-prompt-applied')->self();
    }

    public function updatedValues(mixed $value, ?string $key = null): void
    {
        $this->submitted = false;
        $this->resetValidation($key === null ? null : 'values.'.$key);
    }

    public function submit(): void
    {
        abort_if($this->example === null, 404);

        $this->submitted = false;
        $rules = [];
        $labels = [];

        foreach ($this->fields() as $name => $field) {
            $rules['values.'.$name] = $field['rules'];
            $labels['values.'.$name] = strtolower($field['label']);
        }

        $this->validate($rules, attributes: $labels);
        $this->submitted = true;
        $this->dispatch('example-submitted')->self();
    }

    public function render(): View
    {
        $examples = config('form-examples');
        $definition = $examples[$this->example] ?? null;

        $promptName = $this->promptFieldName();
        $promptField = $this->fields()[$promptName] ?? null;

        return view('livewire.form-examples', compact('examples', 'definition', 'promptName', 'promptField'))
            ->title($this->example === 'dedicated' ? 'Create monitoring policy' : 'Form Examples');
    }

    private function promptFieldName(): string
    {
        return config('form-examples.'.$this->example.'.prompt_field', 'name');
    }

    private function fields(): array
    {
        $sections = config('form-examples.'.$this->example.'.sections', []);

        return array_merge(...array_column($sections, 'fields'));
    }
}
