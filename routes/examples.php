<?php

use App\Livewire\FormExamples;
use Illuminate\Support\Facades\Route;

Route::livewire('/form-examples', FormExamples::class)->name('form-examples.index');
Route::livewire('/form-examples/monitoring-policy', FormExamples::class)->defaults('dedicated', true)->name('form-examples.dedicated');
