<?php

namespace App\Filament\Resources\SubKriteriaResource\Pages;

use App\Filament\Pages\AHPSubkriteriaComparison;
use App\Filament\Resources\KriteriaResource;
use App\Filament\Resources\SubKriteriaResource;
use App\Models\Kriteria;
use App\Models\SubKriteria;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSubKriterias extends ListRecords
{
    protected static string $resource = SubKriteriaResource::class;

    protected function getHeaderActions(): array
    {
        $kriteria_id = request()->get('tableFilters')['kriteria_id']['value'] ?? null;
        
        $actions = [];
        
        // // Tampilkan tombol Pembobotan AHP hanya jika filter kriteria_id ada dan subkriteria >= 2
        // if ($kriteria_id) {
        //     $subkriteriaCount = SubKriteria::where('kriteria_id', $kriteria_id)->count();
        //     $kriteria = Kriteria::find($kriteria_id);
            
        //     if ($subkriteriaCount >= 2 && $kriteria) {
                
        //     }
        // }
        $actions[] = Actions\Action::make('calculate_ahp')
                    ->label('Pembobotan AHP')
                    ->icon('heroicon-o-calculator')
                    ->color('info')
                    ->url(fn (): string => AHPSubkriteriaComparison::getUrl())
                    ->visible(fn () => SubKriteria::count() >= 2);
        // Tambahkan tombol lainnya setelah pembobotan AHP
        $actions[] = Actions\CreateAction::make()
            ->label('New subkriteria');
            
        $actions[] = Actions\Action::make('back_to_kriteria')
            ->label('Kembali ke Kriteria')
            ->icon('heroicon-o-arrow-left')
            ->color('gray')
            ->url(KriteriaResource::getUrl('index'));
        
        return $actions;
    }
}