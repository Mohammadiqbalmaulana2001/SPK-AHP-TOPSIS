<?php

namespace App\Filament\Resources\SubKriteriaResource\Pages;

use App\Filament\Resources\SubKriteriaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSubKriteria extends EditRecord
{
    protected static string $resource = SubKriteriaResource::class;

    // Perbaikan method mount() agar sesuai dengan method induk
    public function mount(string|int $record): void
    {
        parent::mount($record);
        
        // Mengatur kriteria_id untuk resource dari record yang sedang diedit
        SubKriteriaResource::setKriteriaId($this->record->kriteria_id);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->label('Hapus'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index', [
            'tableFilters[kriteria_id][value]' => $this->record->kriteria_id
        ]);
    }
}