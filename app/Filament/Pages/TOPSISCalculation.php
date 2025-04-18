<?php

namespace App\Filament\Pages;

use App\Filament\Resources\PenilaianResource;
use App\Models\Alternatif;
use App\Models\Kriteria;
use App\Models\Penilaian;
use App\Models\SubKriteria;
use App\Services\TOPSISService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Filament\Actions\Action;

class TOPSISCalculation extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calculator';
    protected static ?string $navigationLabel = 'Perhitungan TOPSIS';
    protected static ?string $title = 'Perhitungan Metode TOPSIS';
    protected static string $view = 'filament.pages.topsis-calculation';
    protected static bool $shouldRegisterNavigation = false;
    
    public $topsisResults = null;
    public $isCalculated = false;
    public $showStep = 'matrix';
    
    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Kembali ke penilaian')
                ->icon('heroicon-o-arrow-left')
                ->url(PenilaianResource::getUrl('index'))
                ->color('secondary'),
        ];
    }
    
    public function calculate()
    {
        try {
            // Validasi ketersediaan data
            $alternatifCount = Alternatif::count();
            $kriteriaCount = Kriteria::count();
            $subKriteriaCount = SubKriteria::count();
            $penilaianCount = Penilaian::count();
            
            if ($alternatifCount == 0) {
                throw new \Exception("Belum ada data Alternatif");
            }
            
            if ($kriteriaCount == 0) {
                throw new \Exception("Belum ada data Kriteria");
            }
            
            if ($subKriteriaCount == 0) {
                throw new \Exception("Belum ada data Subkriteria");
            }
            
            if ($penilaianCount == 0) {
                throw new \Exception("Belum ada data Penilaian");
            }
            
            // Validasi kelengkapan data
            $requiredPenilaian = $alternatifCount * $subKriteriaCount;
            if ($penilaianCount < $requiredPenilaian) {
                throw new \Exception("Data penilaian tidak lengkap. Diperlukan $requiredPenilaian data, tersedia $penilaianCount data.");
            }
            
            // Validasi bobot kriteria
            $kriteriaWithoutWeight = Kriteria::where('bobot', 0)->first();
            if ($kriteriaWithoutWeight) {
                throw new \Exception("Kriteria '{$kriteriaWithoutWeight->nama}' belum memiliki bobot. Lakukan pembobotan dengan AHP terlebih dahulu.");
            }
            
            // Validasi bobot subkriteria
            $subKriteriaWithoutWeight = SubKriteria::where('bobot_global', 0)->first();
            if ($subKriteriaWithoutWeight) {
                throw new \Exception("Subkriteria '{$subKriteriaWithoutWeight->nama}' belum memiliki bobot. Lakukan pembobotan dengan AHP terlebih dahulu.");
            }
            
            // Hitung dengan TOPSIS Service
            $topsisService = new TOPSISService();
            $this->topsisResults = $topsisService->process();
            $this->isCalculated = true;
            
            Notification::make()
                ->title('Berhasil melakukan perhitungan TOPSIS')
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
    
    public function setStep($step)
    {
        $this->showStep = $step;
    }
}