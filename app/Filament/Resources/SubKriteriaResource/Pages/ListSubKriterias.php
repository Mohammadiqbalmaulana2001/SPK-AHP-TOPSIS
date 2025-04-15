<?php

namespace App\Filament\Resources\SubKriteriaResource\Pages;

use App\Filament\Resources\KriteriaResource;
use App\Filament\Resources\SubKriteriaResource;
use App\Models\Kriteria;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSubKriterias extends ListRecords
{
    protected static string $resource = SubKriteriaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('back_to_kriteria')
                ->label('Kembali ke Kriteria')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(KriteriaResource::getUrl('index'))
        ];
    }
    
    public function mount(): void
    {
        parent::mount();
        
        // Mendapatkan kriteria_id dari parameter URL jika ada
        $kriteriaId = request()->get('tableFilters.kriteria_id.value');
        
        if ($kriteriaId) {
            // Mengatur judul halaman berdasarkan kriteria yang dipilih
            $kriteria = Kriteria::find($kriteriaId);
            if ($kriteria) {
                $this->heading = "Daftar Sub Kriteria untuk {$kriteria->nama}";
            }
        }
    }
}
