<?php

namespace App\Filament\Pages;

use App\Models\Kriteria;
use App\Models\SubKriteria;
use App\Services\AHPSubkriteriaService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Filament\Actions\Action;
use App\Filament\Resources\SubKriteriaResource;

class AHPSubkriteriaComparison extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calculator';
    protected static ?string $navigationLabel = 'Perbandingan Subkriteria (AHP)';
    protected static ?string $title = 'Pembobotan Subkriteria dengan Metode AHP';
    protected static bool $shouldRegisterNavigation = false;
    protected static string $view = 'filament.pages.ahp-subkriteria-comparison';
    
    public $kriteriaId;
    public $kriteria = null;
    public $subKriterias = [];
    public $comparisonValues = [];
    public $ahpResults = null;
    public $selectedKriteriaId = null;
    
    public function mount($kriteriaId = null)
    {
        $this->selectedKriteriaId = $kriteriaId;
        
        if ($this->selectedKriteriaId) {
            $this->loadKriteria($this->selectedKriteriaId);
        }
    }
    
    public function loadKriteria($kriteriaId)
    {
        try {
            $this->kriteriaId = $kriteriaId;
            $this->kriteria = Kriteria::find($kriteriaId);
            
            if (!$this->kriteria) {
                throw new \Exception("Kriteria tidak ditemukan");
            }
            
            $this->subKriterias = SubKriteria::where('kriteria_id', $kriteriaId)->get();
            $this->initializeComparisonValues();
            $this->ahpResults = null;
        } catch (\Exception $e) {
            $this->resetKriteriaData();
            Notification::make()
                ->title('Error')
                ->body($e->getMessage())
                ->warning()
                ->send();
        }
    }
    
    protected function resetKriteriaData()
    {
        $this->kriteria = null;
        $this->subKriterias = [];
        $this->comparisonValues = [];
        $this->ahpResults = null;
    }
    
    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Kembali ke Subkriteria')
                ->icon('heroicon-o-arrow-left')
                ->url(fn (): string => $this->kriteriaId 
                    ? SubKriteriaResource::getUrl('index', ['tableFilters[kriteria_id][value]' => $this->kriteriaId])
                    : SubKriteriaResource::getUrl('index'))
                ->color('secondary'),
        ];
    }
    
    protected function initializeComparisonValues()
    {
        $this->comparisonValues = [];
        
        if ($this->subKriterias->count() < 2) {
            return;
        }
        
        $subKriteriaIds = $this->subKriterias->pluck('id')->toArray();
        
        for ($i = 0; $i < count($this->subKriterias); $i++) {
            for ($j = $i + 1; $j < count($this->subKriterias); $j++) {
                $sub1 = $this->subKriterias[$i];
                $sub2 = $this->subKriterias[$j];
                
                if (!in_array($sub1->id, $subKriteriaIds) || !in_array($sub2->id, $subKriteriaIds)) {
                    continue;
                }
                
                $key = $sub1->id . '_' . $sub2->id;
                $this->comparisonValues[$key] = [
                    'subkriteria1_id' => $sub1->id,
                    'subkriteria2_id' => $sub2->id,
                    'nilai' => 1,
                    'inverse' => false
                ];
            }
        }
    }
    
    public function form(Form $form): Form
    {
        $fieldGroups = [];
        
        $fieldGroups[] = Forms\Components\Section::make('Pilih Kriteria')
            ->schema([
                Forms\Components\Select::make('selectedKriteriaId')
                    ->label('Kriteria')
                    ->options(Kriteria::all()->pluck('nama', 'id'))
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state) {
                        if ($state) {
                            $this->loadKriteria($state);
                        } else {
                            $this->resetKriteriaData();
                        }
                    }),
            ]);
        
        if (!$this->selectedKriteriaId || !$this->kriteria) {
            $fieldGroups[] = Forms\Components\Section::make('Perbandingan Subkriteria')
                ->schema([
                    Forms\Components\Placeholder::make('info')
                        ->content('Silakan pilih kriteria terlebih dahulu untuk melakukan perbandingan subkriteria.')
                        ->columnSpanFull(),
                ]);
            
            return $form->schema($fieldGroups);
        }
        
        if ($this->subKriterias->count() < 2) {
            $fieldGroups[] = Forms\Components\Section::make('Perbandingan Subkriteria')
                ->schema([
                    Forms\Components\Placeholder::make('info')
                        ->content('Minimal 2 subkriteria diperlukan untuk melakukan perbandingan.')
                        ->columnSpanFull(),
                ]);
            
            return $form->schema($fieldGroups);
        }
        
        $fields = [];
        
        foreach ($this->comparisonValues as $key => $comparison) {
            $parts = explode('_', $key);
            $sub1 = $this->subKriterias->firstWhere('id', $parts[0]);
            $sub2 = $this->subKriterias->firstWhere('id', $parts[1]);
            
            if (!$sub1 || !$sub2) {
                continue;
            }
            
            $fields[] = Forms\Components\Grid::make(3)
                ->schema([
                    Forms\Components\Placeholder::make("subkriteria_left_{$key}")
                        ->content($sub1->nama)
                        ->extraAttributes(['class' => 'text-right font-bold']),
                        
                    Forms\Components\Select::make("comparisonValues.{$key}.nilai")
                        ->options([
                            9 => '9 - Mutlak lebih penting',
                            8 => '8 - Sangat lebih penting',
                            // ... opsi lainnya ...
                            0.11111111111111 => '1/9 - Mutlak kurang penting',
                        ])
                        ->default(1),
                        
                    Forms\Components\Placeholder::make("subkriteria_right_{$key}")
                        ->content($sub2->nama)
                        ->extraAttributes(['class' => 'font-bold']),
                        
                    Forms\Components\Toggle::make("comparisonValues.{$key}.inverse")
                        ->label("Balik Perbandingan")
                        ->columnSpanFull(),
                ]);
        }
        
        $fieldGroups[] = Forms\Components\Section::make('Perbandingan Subkriteria')
            ->description('Berikan nilai perbandingan untuk setiap pasangan subkriteria pada kriteria ' . $this->kriteria->nama)
            ->schema($fields);
            
        return $form->schema($fieldGroups);
    }
    
    public function calculate()
    {
        if (!$this->selectedKriteriaId || !$this->kriteria) {
            Notification::make()
                ->title('Pilih kriteria yang valid terlebih dahulu')
                ->warning()
                ->send();
            return;
        }
        
        if ($this->subKriterias->count() < 2) {
            Notification::make()
                ->title('Minimal 2 subkriteria diperlukan untuk melakukan perbandingan')
                ->warning()
                ->send();
            return;
        }
        
        try {
            $pairwiseValues = [];
            $subKriteriaIds = $this->subKriterias->pluck('id')->toArray();
            
            foreach ($this->comparisonValues as $key => $comparison) {
                $parts = explode('_', $key);
                if (count($parts) !== 2) continue;
                
                $subkriteria1_id = (int)$parts[0];
                $subkriteria2_id = (int)$parts[1];
                
                if (!in_array($subkriteria1_id, $subKriteriaIds) || 
                    !in_array($subkriteria2_id, $subKriteriaIds)) {
                    continue;
                }
                
                $nilai = (float)$comparison['nilai'];
                
                if ($comparison['inverse']) {
                    $temp = $subkriteria1_id;
                    $subkriteria1_id = $subkriteria2_id;
                    $subkriteria2_id = $temp;
                    $nilai = 1/$nilai;
                }
                
                $pairwiseValues[] = [
                    'subkriteria1_id' => $subkriteria1_id,
                    'subkriteria2_id' => $subkriteria2_id,
                    'nilai' => $nilai
                ];
            }
            
            $ahpService = new AHPSubkriteriaService();
            $this->ahpResults = $ahpService->process($pairwiseValues, $subKriteriaIds);
            
            $kriteriaWeight = ($this->kriteria->bobot ?? 0) / 100;
            $this->ahpResults['global_weights'] = $ahpService->calculateGlobalWeights(
                $this->ahpResults['weights'], 
                $kriteriaWeight
            );
            
            $this->saveSubkriteriaWeights();
            
            Notification::make()
                ->title('Berhasil menghitung perbandingan subkriteria')
                ->success()
                ->send();
                
        } catch (\Exception $e) {
            Notification::make()
                ->title('Gagal menghitung')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
    
    protected function saveSubkriteriaWeights()
    {
        if (!$this->ahpResults || !isset($this->ahpResults['weights']) || !isset($this->ahpResults['global_weights'])) {
            return;
        }
        
        DB::beginTransaction();
        
        try {
            foreach ($this->ahpResults['weights'] as $subKriteriaId => $weight) {
                $globalWeight = $this->ahpResults['global_weights'][$subKriteriaId];
                
                SubKriteria::where('id', $subKriteriaId)->update([
                    'bobot' => $weight * 100,
                    'bobot_global' => $globalWeight * 100
                ]);
            }
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Notification::make()
                ->title('Gagal menyimpan bobot')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
    
    public function beforeRender(): void
    {
        if ($this->selectedKriteriaId && !$this->kriteria) {
            $this->loadKriteria($this->selectedKriteriaId);
        }
    }
}