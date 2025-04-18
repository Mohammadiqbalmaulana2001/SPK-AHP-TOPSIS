<?php

namespace App\Filament\Resources\PenilaianResource\Pages;

use App\Filament\Pages\TOPSISCalculation;
use App\Filament\Resources\PenilaianResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPenilaians extends ListRecords
{
    protected static string $resource = PenilaianResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('topsis')
                ->label('Perhitungan TOPSIS')
                ->icon('heroicon-o-calculator')
                ->color('info')
                ->url(fn (): string => TOPSISCalculation::getUrl()),
            Actions\CreateAction::make()
                ->label('Tambah Penilaian'),
        ];
    }
    
}
