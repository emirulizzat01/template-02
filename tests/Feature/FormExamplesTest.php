<?php

namespace Tests\Feature;

use App\Livewire\DialogExamples;
use App\Livewire\FormExamples;
use Livewire\Livewire;
use Tests\TestCase;

class FormExamplesTest extends TestCase
{
    public function test_modal_prompt_updates_the_form_and_preserves_unsaved_input_until_confirmed(): void
    {
        Livewire::test(FormExamples::class)
            ->call('openExample', 'modal')
            ->set('values.title', 'Original title')
            ->call('openTitlePrompt')
            ->assertSet('promptValue', 'Original title')
            ->set('promptValue', '')
            ->call('applyTitlePrompt')
            ->assertHasErrors('promptValue')
            ->assertNotDispatched('example-prompt-applied')
            ->set('promptValue', ' Updated title ')
            ->call('applyTitlePrompt')
            ->assertSet('values.title', 'Updated title')
            ->assertDispatched('example-prompt-applied')
            ->call('requestDiscard')
            ->assertDispatched('example-discard-requested')
            ->assertSet('values.title', 'Updated title')
            ->call('discard')
            ->assertDispatched('example-close-requested')
            ->call('closeExample')
            ->assertSet('values', [])
            ->assertSet('promptValue', '');
    }

    public function test_all_form_layouts_validate_before_dispatching_a_submission_alert(): void
    {
        foreach (['modal', 'drawer', 'dedicated'] as $example) {
            $component = $example === 'dedicated'
                ? Livewire::test(FormExamples::class, ['dedicated' => true])
                : Livewire::test(FormExamples::class)->call('openExample', $example);

            $component->call('submit')
                ->assertHasErrors()
                ->assertNotDispatched('example-submitted')
                ->call('fillSample')
                ->call('submit')
                ->assertHasNoErrors()
                ->assertSet('submitted', true)
                ->assertDispatched('example-submitted');
        }
    }

    public function test_drawer_validates_timeout_against_the_interval(): void
    {
        Livewire::test(FormExamples::class)
            ->call('openExample', 'drawer')
            ->call('fillSample')
            ->set('values.timeout', 120)
            ->call('submit')
            ->assertHasErrors('values.timeout')
            ->assertSet('submitted', false);
    }

    public function test_standalone_prompt_trims_validates_and_resets_its_input(): void
    {
        Livewire::test(DialogExamples::class)
            ->set('label', ' ')
            ->call('submitLabel')
            ->assertHasErrors('label')
            ->set('label', ' Gateway API ')
            ->call('submitLabel')
            ->assertHasNoErrors()
            ->assertDispatched('dialog-example-label-accepted', label: 'Gateway API')
            ->assertSet('label', '');
    }
}
