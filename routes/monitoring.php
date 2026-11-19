<?php

use App\Livewire\DeploymentsIndex;
use App\Livewire\IncidentsIndex;
use App\Livewire\InfrastructureOverview;
use Illuminate\Support\Facades\Route;

Route::livewire('/', InfrastructureOverview::class)->name('dashboard');
Route::livewire('/deployments', DeploymentsIndex::class)->name('deployments');
Route::livewire('/incidents', IncidentsIndex::class)->name('incidents');
