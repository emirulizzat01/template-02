<?php

namespace App\Support;

use Illuminate\Support\Facades\Route;

class Navigation
{
    public function groups(): array
    {
        $groups = [];

        foreach (config('template.navigation', []) as $heading => $items) {
            foreach ($items as $item) {
                $enabled = ! isset($item['module']) || config('template.modules.'.$item['module']);

                if ($enabled && Route::has($item['route'])) {
                    $item['href'] = route($item['route']).(isset($item['fragment']) ? '#'.$item['fragment'] : '');
                    $groups[$heading][] = $item;
                }
            }
        }

        return $groups;
    }

    public function searchablePages(): array
    {
        $pages = [];

        foreach ($this->groups() as $items) {
            foreach ($items as $item) {
                $pages[$item['search_label'] ?? $item['label']] = $item['href'];
            }
        }

        return $pages;
    }
}
