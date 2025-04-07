<x-filament::page>
    <x-filament::section>
        <h2 class="text-xl font-bold mb-4">Perbandingan Kriteria dengan Metode AHP</h2>
        <p class="mb-6">Metode Analytical Hierarchy Process (AHP) digunakan untuk menentukan bobot kriteria berdasarkan perbandingan berpasangan.</p>
        
        {{ $this->form }}
        
        <div class="mt-4">
            <x-filament::button wire:click="calculate">
                Hitung Bobot Kriteria
            </x-filament::button>
        </div>
        
        @if ($ahpResults)
            <div class="mt-8">
                <h3 class="text-lg font-bold mb-2">Hasil Perhitungan AHP</h3>
                
                <div class="mb-6">
                    <h4 class="font-medium mb-2">Matriks Perbandingan Berpasangan</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y ">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2  text-left"></th>
                                    @foreach ($kriterias as $kriteria)
                                        <th class="px-4 py-2  text-left">{{ $kriteria->nama }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y ">
                                @foreach ($kriterias as $i => $kriteria1)
                                    <tr>
                                        <td class="px-4 py-2 font-medium">{{ $kriteria1->nama }}</td>
                                        @foreach ($kriterias as $j => $kriteria2)
                                            <td class="px-4 py-2">{{ number_format($ahpResults['matrix'][$i][$j], 3) }}</td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="mb-6">
                    <h4 class="font-medium mb-2">Matriks Ternormalisasi</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y ">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2  text-left"></th>
                                    @foreach ($kriterias as $kriteria)
                                        <th class="px-4 py-2  text-left">{{ $kriteria->nama }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y ">
                                @foreach ($kriterias as $i => $kriteria1)
                                    <tr>
                                        <td class="px-4 py-2 font-medium">{{ $kriteria1->nama }}</td>
                                        @foreach ($kriterias as $j => $kriteria2)
                                            <td class="px-4 py-2">{{ number_format($ahpResults['normalized_matrix'][$i][$j], 3) }}</td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="mb-6">
                    <h4 class="font-medium mb-2">Bobot Kriteria (Priority Vector)</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y ">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2  text-left">Kriteria</th>
                                    <th class="px-4 py-2  text-left">Bobot</th>
                                    <th class="px-4 py-2  text-left">Persentase</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y ">
                                @foreach ($kriterias as $i => $kriteria)
                                    <tr>
                                        <td class="px-4 py-2 font-medium">{{ $kriteria->nama }}</td>
                                        <td class="px-4 py-2">{{ number_format($ahpResults['priority_vector'][$i], 4) }}</td>
                                        <td class="px-4 py-2">{{ number_format($ahpResults['priority_vector'][$i] * 100, 2) }}%</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="mb-6">
                    <h4 class="font-medium mb-2">Konsistensi Penilaian</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="p-4  rounded">
                            <p class="text-sm font-medium">Lambda Max</p>
                            <p class="text-xl">{{ number_format($ahpResults['lambda_max'], 4) }}</p>
                        </div>
                        <div class="p-4  rounded">
                            <p class="text-sm font-medium">Consistency Index (CI)</p>
                            <p class="text-xl">{{ number_format($ahpResults['consistency_index'], 4) }}</p>
                        </div>
                        <div class="p-4 {{ $ahpResults['is_consistent'] ? 'bg-green-50' : 'bg-red-50' }} rounded">
                            <p class="text-sm font-medium">Consistency Ratio (CR)</p>
                            <p class="text-xl {{ $ahpResults['is_consistent'] ? 'text-green-600' : 'text-red-600' }}">
                                {{ number_format($ahpResults['consistency_ratio'], 4) }}
                                @if ($ahpResults['is_consistent'])
                                    <span class="text-sm">(Konsisten)</span>
                                @else
                                    <span class="text-sm">(Tidak Konsisten)</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    
                    @if (!$ahpResults['is_consistent'])
                        <div class="mt-4 p-4 bg-red-50  rounded">
                            <p><span class="font-bold text-red-700">Peringatan:</span> Nilai CR > 0.1 menunjukkan bahwa penilaian perbandingan kriteria tidak konsisten. Mohon lakukan perbandingan ulang.</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </x-filament::section>
</x-filament::page>