<?php

namespace App\Filament\Resources\KriteriaResource\Pages;

use App\Filament\Pages\AHPKriteriaComparison;
use App\Filament\Resources\KriteriaResource;
use App\Models\Kriteria;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKriterias extends ListRecords
{
    protected static string $resource = KriteriaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New kriteria'),
            Actions\Action::make('calculate_ahp')
                ->label('Hitung AHP Kriteria')
                ->icon('heroicon-o-calculator')
                ->color('info')
                ->url(fn (): string => AHPKriteriaComparison::getUrl())
                ->visible(fn () => Kriteria::count() >= 2),
        ];
    }
}