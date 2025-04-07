<?php

namespace App\Filament\Pages;

use App\Services\TopsisService;
use Filament\Pages\Page;

class HasilTopsis extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';
    protected static string $view = 'filament.pages.hasil-topsis';
    protected static ?string $navigationGroup = 'Analisis';
    protected static ?string $title = 'Hasil Akhir TOPSIS';
    protected static ?int $navigationSort = 2;

    public array $results = [];

    public function mount(): void
    {
        $service = new TopsisService();
        $this->results = $service->calculate();
    }
}
