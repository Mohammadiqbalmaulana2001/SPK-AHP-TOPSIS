<x-filament::page>
    <x-filament::section>
        <h2 class="text-xl font-bold mb-4">
            Perbandingan Subkriteria
            @if($kriteria)
                untuk Kriteria: {{ $kriteria->nama }}
            @endif
        </h2>
        <p class="my-6 py-4">Metode Analytical Hierarchy Process (AHP) digunakan untuk menentukan bobot subkriteria berdasarkan perbandingan berpasangan.</p>
        
        {{ $this->form }}
        
        @if($selectedKriteriaId)
            <div class="py-4">
                <x-filament::button wire:click="calculate" icon="heroicon-o-calculator">
                    Hitung Bobot Subkriteria
                </x-filament::button>
            </div>
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
        @endif
    </x-filament::section>
</x-filament::page>