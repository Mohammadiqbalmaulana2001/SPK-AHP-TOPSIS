<x-filament::page>
    <x-filament::section>
        <h2 class="text-xl font-bold mb-4">
            Perbandingan Subkriteria
            @if($kriteria)
                untuk Kriteria: {{ $kriteria->nama }}
            @endif
        </h2>
        
        <p class="my-6 py-4">Metode Analytical Hierarchy Process (AHP) digunakan untuk menentukan bobot subkriteria berdasarkan perbandingan berpasangan.</p>
        
        <!-- Pilihan Kriteria -->
        <div class="mb-6">
            <x-filament::input.wrapper
                :id="$this->getId('kriteria')"
                label="Pilih Kriteria"
                required="true"
            >
                <x-filament::input.select
                    wire:model.live="selectedKriteriaId"
                    :placeholder="__('Pilih kriteria')"
                >
                    <option value="">Pilih Kriteria</option>
                    @foreach(\App\Models\Kriteria::all() as $k)
                        <option value="{{ $k->id }}">{{ $k->nama }}</option>
                    @endforeach
                </x-filament::input.select>
            </x-filament::input.wrapper>
        </div>
        
        @if($selectedKriteriaId && !$kriteria)
            <div class="py-2 px-4 bg-yellow-50 text-yellow-700 rounded-md">
                Kriteria tidak ditemukan. Silakan pilih kriteria lain.
            </div>
        @endif
        
        @if($kriteria)
            @if(count($subKriterias) < 2)
                <div class="py-2 px-4 bg-yellow-50 text-yellow-700 rounded-md">
                    Minimal 2 subkriteria diperlukan untuk melakukan perbandingan.
                </div>
            @else
                <!-- Form Perbandingan -->
                <div class="my-6">
                    <h3 class="text-lg font-semibold mb-4 py-8">Perbandingan Berpasangan Subkriteria</h3>
                    
                    @foreach($comparisonValues as $key => $comparison)
                        @php
                            $parts = explode('_', $key);
                            $subkriteria1 = $subKriterias->firstWhere('id', $parts[0]);
                            $subkriteria2 = $subKriterias->firstWhere('id', $parts[1]);
                            
                            // Skip invalid comparisons
                            if(!$subkriteria1 || !$subkriteria2) continue;
                        @endphp
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-2 items-center mb-4">
                            <div class="text-right font-medium">{{ $subkriteria1->nama }}</div>
                            
                            <div>
                                <x-filament::input.wrapper :id="$this->getId('comparison-' . $key)">
                                    <x-filament::input.select wire:model.live="comparisonValues.{{ $key }}.nilai">
                                        <option value="9">9 - Mutlak lebih penting</option>
                                        <option value="8">8 - Sangat lebih penting</option>
                                        <option value="7">7 - Lebih penting</option>
                                        <option value="6">6 - Cukup lebih penting</option>
                                        <option value="5">5 - Lebih penting</option>
                                        <option value="4">4 - Sedikit lebih penting</option>
                                        <option value="3">3 - Cukup penting</option>
                                        <option value="2">2 - Sedikit penting</option>
                                        <option value="1">1 - Sama penting</option>
                                        <option value="0.5">1/2 - Sedikit kurang penting</option>
                                        <option value="0.33333333333333">1/3 - Cukup kurang penting</option>
                                        <option value="0.25">1/4 - Sedikit kurang penting</option>
                                        <option value="0.2">1/5 - Kurang penting</option>
                                        <option value="0.16666666666667">1/6 - Cukup kurang penting</option>
                                        <option value="0.14285714285714">1/7 - Kurang penting</option>
                                        <option value="0.125">1/8 - Sangat kurang penting</option>
                                        <option value="0.11111111111111">1/9 - Mutlak kurang penting</option>
                                    </x-filament::input.select>
                                </x-filament::input.wrapper>
                            </div>
                            
                            <div class="font-medium">{{ $subkriteria2->nama }}</div>
                            
                            <div class="md:col-start-2 flex items-center justify-center">
                                <x-filament::input.wrapper :id="$this->getId('inverse-' . $key)">
                                    {{-- <label class="flex items-center">
                                        <x-filament::input.checkbox
                                            wire:model.live="comparisonValues.{{ $key }}.inverse"
                                        />
                                        <span class="ml-2">Balik Perbandingan</span>
                                    </label> --}}
                                </x-filament::input.wrapper>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <div class="py-4">
                    <x-filament::button wire:click="calculate" icon="heroicon-o-calculator">
                        Hitung Bobot Subkriteria
                    </x-filament::button>
                </div>
            @endif
        @endif
        
        @if ($ahpResults)
            <div class="space-y-8">
                <h1 class="text-lg font-bold py-6">Hasil Perhitungan AHP</h1>
                
                <!-- Matriks Perbandingan Berpasangan -->
                <div class="rounded-lg border border-gray-200">
                    <div class="px-6 py-3 border-b border-gray-200">
                        <h4 class="font-medium text-orange-600">Matriks Perbandingan Berpasangan</h4>
                    </div>
                    <div class="w-full overflow-x-auto p-1">
                        <table class="w-full divide-y">
                            <thead>
                                <tr>
                                    <th class="sticky left-0 px-4 py-2 text-left text-sm font-medium">Subkriteria</th>
                                    @foreach ($subKriterias as $subkriteria)
                                        <th class="px-4 py-2 text-center text-sm font-medium min-w-[100px]">
                                            {{ $subkriteria->nama }}
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @foreach ($subKriterias as $i => $subkriteria1)
                                    <tr>
                                        <td class="sticky left-0 px-4 py-2 whitespace-nowrap text-sm font-medium">{{ $subkriteria1->nama }}</td>
                                        @foreach ($subKriterias as $j => $subkriteria2)
                                            <td class="px-4 py-2 text-center text-sm {{ $i == $j ? 'text-gray-400' : '' }}">
                                                {{ number_format($ahpResults['matrix'][$i][$j], 3) }}
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Matriks Ternormalisasi -->
                <div class="rounded-lg border border-gray-200 mt-6">
                    <div class="px-6 py-3 border-b border-gray-200">
                        <h4 class="font-medium">Matriks Ternormalisasi</h4>
                    </div>
                    <div class="w-full overflow-x-auto p-1">
                        <table class="w-full divide-y">
                            <thead>
                                <tr>
                                    <th class="sticky left-0 px-4 py-2 text-left text-sm font-medium">Subkriteria</th>
                                    @foreach ($subKriterias as $subkriteria)
                                        <th class="px-4 py-2 text-center text-sm font-medium min-w-[100px]">
                                            {{ $subkriteria->nama }}
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @foreach ($subKriterias as $i => $subkriteria1)
                                    <tr>
                                        <td class="sticky left-0 px-4 py-2 whitespace-nowrap text-sm font-medium">{{ $subkriteria1->nama }}</td>
                                        @foreach ($subKriterias as $j => $subkriteria2)
                                            <td class="px-4 py-2 text-center text-sm">
                                                {{ number_format($ahpResults['normalized_matrix'][$i][$j], 3) }}
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Bobot Subkriteria -->
                <div class="rounded-lg border border-gray-200 mt-6">
                    <div class="px-6 py-3 border-b border-gray-200">
                        <h4 class="font-medium">Bobot Subkriteria (Priority Vector)</h4>
                    </div>
                    <div class="w-full overflow-x-auto p-1">
                        <table class="w-full divide-y">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 text-left text-sm font-medium">Subkriteria</th>
                                    <th class="px-4 py-2 text-center text-sm font-medium">Bobot Lokal</th>
                                    <th class="px-4 py-2 text-center text-sm font-medium">% Lokal</th>
                                    <th class="px-4 py-2 text-center text-sm font-medium">Bobot Global</th>
                                    <th class="px-4 py-2 text-center text-sm font-medium">% Global</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @foreach ($subKriterias as $i => $subkriteria)
                                    <tr>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm font-medium">{{ $subkriteria->nama }}</td>
                                        <td class="px-4 py-2 text-center text-sm">
                                            {{ number_format($ahpResults['priority_vector'][$i], 4) }}
                                        </td>
                                        <td class="px-4 py-2 text-center text-sm">
                                            {{ number_format($ahpResults['priority_vector'][$i] * 100, 2) }}%
                                        </td>
                                        <td class="px-4 py-2 text-center text-sm">
                                            {{ number_format($ahpResults['global_weights'][$subkriteria->id], 4) }}
                                        </td>
                                        <td class="px-4 py-2 text-center text-sm">
                                            {{ number_format($ahpResults['global_weights'][$subkriteria->id] * 100, 2) }}%
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Konsistensi Penilaian -->
                <div class="rounded-lg border border-gray-200 mt-6">
                    <div class="px-6 py-3 border-b border-gray-200">
                        <h4 class="font-medium">Konsistensi Penilaian</h4>
                    </div>
                    <div class="p-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="p-3 border rounded-lg">
                                <p class="text-sm">Lambda Max</p>
                                <p class="text-lg font-semibold">{{ number_format($ahpResults['lambda_max'], 4) }}</p>
                            </div>
                            <div class="p-3 border rounded-lg">
                                <p class="text-sm">Consistency Index (CI)</p>
                                <p class="text-lg font-semibold">{{ number_format($ahpResults['consistency_index'], 4) }}</p>
                            </div>
                            <div class="p-3 border rounded-lg {{ $ahpResults['is_consistent'] ? 'border-green-200' : 'border-red-200' }}">
                                <p class="text-sm">Consistency Ratio (CR)</p>
                                <p class="text-lg font-semibold {{ $ahpResults['is_consistent'] ? 'text-green-600' : 'text-red-600' }}">
                                    {{ number_format($ahpResults['consistency_ratio'], 4) }}
                                    <span class="text-sm ml-2">
                                        ({{ $ahpResults['is_consistent'] ? 'Konsisten' : 'Tidak Konsisten' }})
                                    </span>
                                </p>
                            </div>
                        </div>
                        
                        @if (!$ahpResults['is_consistent'])
                            <div class="mt-4 py-3 rounded-lg">
                                <p class="text-sm text-red-600">
                                    <span class="font-medium">Peringatan:</span> Nilai CR > 0.1 menunjukkan ketidakkonsistenan. Mohon lakukan perbandingan ulang.
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="flex justify-center gap-4 mt-6">
                <x-filament::button 
                    wire:click="exportToPDF" 
                    icon="heroicon-o-document-arrow-down"
                    color="danger"
                    size="xs"
                    class="text-xs px-2 py-2"
                >
                    Unduh PDF
                </x-filament::button>
                <x-filament::button 
                    wire:click="exportToExcel" 
                    icon="heroicon-o-table-cells"
                    color="success"
                    class="text-xs px-2 py-2"
                    size="xs"
                >
                    Unduh Excel
                </x-filament::button>
        @endif
    </x-filament::section>
</x-filament::page>