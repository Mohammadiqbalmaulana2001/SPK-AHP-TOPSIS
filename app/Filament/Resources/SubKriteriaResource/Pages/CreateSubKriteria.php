<?php

namespace App\Filament\Resources\SubKriteriaResource\Pages;

use App\Filament\Resources\SubKriteriaResource;
use App\Models\Kriteria;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSubKriteria extends CreateRecord
{
    protected static string $resource = SubKriteriaResource::class;
    // protected function getRedirectUrl(): string
    // {
    //     return $this->getResource()::getUrl('index');
    // }
    public function mount(): void
    {
        parent::mount();
        
        // Mendapatkan kriteria_id dari parameter URL
        $kriteriaId = request()->get('kriteria_id');
        
        if ($kriteriaId) {
            // Mengisi formulir dengan kriteria_id yang diberikan
            $this->form->fill([
                'kriteria_id' => $kriteriaId,
            ]);
            
            // Mengubah judul halaman sesuai dengan kriteria yang dipilih
            $kriteria = Kriteria::find($kriteriaId);
            if ($kriteria) {
                $this->heading = "Tambah Sub Kriteria untuk {$kriteria->nama}";
            }
        }
    }

    protected function getRedirectUrl(): string
    {
        $kriteriaId = $this->record->kriteria_id;
        
        return $this->getResource()::getUrl('index');
    }
}
