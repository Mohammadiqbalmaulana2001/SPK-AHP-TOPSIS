<x-filament::page>
    <x-filament::section>
        <h2 class="text-xl font-bold mb-4">Perhitungan dengan Metode TOPSIS</h2>
        <p class="my-6 py-4">
            Technique for Order of Preference by Similarity to Ideal Solution (TOPSIS) adalah metode pengambilan keputusan multikriteria
            yang mengidentifikasi solusi dari alternatif berdasarkan jarak terpendek dari solusi ideal positif
            dan jarak terjauh dari solusi ideal negatif.
        </p>
        
        <div class="py-4">
            <x-filament::button wire:click="calculate" icon="heroicon-o-calculator">
                Hitung dengan TOPSIS
            </x-filament::button>
        </div>
        
        @if ($isCalculated && $topsisResults)
            <div class="space-y-4 mt-6">
                <!-- Navigation Tabs -->
                <div class="flex flex-wrap gap-2 mb-6">
                    <x-filament::button 
                        wire:click="setStep('matrix')" 
                        :color="$showStep === 'matrix' ? 'primary' : 'secondary'">
                        Matrix Keputusan
                    </x-filament::button>
                    <x-filament::button 
                        wire:click="setStep('normalized')" 
                        :color="$showStep === 'normalized' ? 'primary' : 'secondary'">
                        Matrix Normalisasi
                    </x-filament::button>
                    <x-filament::button 
                        wire:click="setStep('weighted')" 
                        :color="$showStep === 'weighted' ? 'primary' : 'secondary'">
                        Matrix Terbobot
                    </x-filament::button>
                    <x-filament::button 
                        wire:click="setStep('ideal')" 
                        :color="$showStep === 'ideal' ? 'primary' : 'secondary'">
                        Solusi Ideal
                    </x-filament::button>
                    <x-filament::button 
                        wire:click="setStep('distance')" 
                        :color="$showStep === 'distance' ? 'primary' : 'secondary'">
                        Jarak Solusi
                    </x-filament::button>
                    <x-filament::button 
                        wire:click="setStep('results')" 
                        :color="$showStep === 'results' ? 'primary' : 'secondary'">
                        Hasil Akhir
                    </x-filament::button>
                </div>
                
                <!-- Matrix Keputusan -->
                @if ($showStep === 'matrix')
                <div class="rounded-lg border border-gray-200">
                    <div class="px-6 py-3 border-b border-gray-200">
                        <h4 class="font-medium text-orange-600">Matrix Keputusan</h4>
                    </div>
                    <div class="w-full overflow-x-auto p-1">
                        <table class="w-full divide-y">
                            <thead>
                                <tr>
                                    <th class="sticky left-0 px-4 py-2 text-left text-sm font-medium">Alternatif</th>
                                    @foreach ($topsisResults['subKriterias'] as $subKriteria)
                                        <th class="px-4 py-2 text-center text-sm font-medium min-w-[100px]">
                                            {{ $subKriteria->kode }} ({{ $subKriteria->kriteria->tipe }})
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @foreach ($topsisResults['alternatifs'] as $alternatif)
                                    <tr>
                                        <td class="sticky left-0 px-4 py-2 whitespace-nowrap text-sm font-medium">
                                            {{ $alternatif->kode }} - {{ $alternatif->nama }}
                                        </td>
                                        @foreach ($topsisResults['subKriterias'] as $subKriteria)
                                            <td class="px-4 py-2 text-center text-sm">
                                                {{ $topsisResults['decisionMatrix'][$alternatif->id][$subKriteria->id] }}
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
                
                <!-- Matrix Normalisasi -->
                @if ($showStep === 'normalized')
                <div class="rounded-lg border border-gray-200">
                    <div class="px-6 py-3 border-b border-gray-200">
                        <h4 class="font-medium text-orange-600">Matrix Normalisasi</h4>
                    </div>
                    <div class="w-full overflow-x-auto p-1">
                        <table class="w-full divide-y">
                            <thead>
                                <tr>
                                    <th class="sticky left-0 px-4 py-2 text-left text-sm font-medium">Alternatif</th>
                                    @foreach ($topsisResults['subKriterias'] as $subKriteria)
                                        <th class="px-4 py-2 text-center text-sm font-medium min-w-[100px]">
                                            {{ $subKriteria->kode }}
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @foreach ($topsisResults['alternatifs'] as $alternatif)
                                    <tr>
                                        <td class="sticky left-0 px-4 py-2 whitespace-nowrap text-sm font-medium">
                                            {{ $alternatif->kode }} - {{ $alternatif->nama }}
                                        </td>
                                        @foreach ($topsisResults['subKriterias'] as $subKriteria)
                                            <td class="px-4 py-2 text-center text-sm">
                                                {{ number_format($topsisResults['normalizedMatrix'][$alternatif->id][$subKriteria->id], 4) }}
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
                
                <!-- Matrix Normalisasi Terbobot -->
                @if ($showStep === 'weighted')
                <div class="rounded-lg border border-gray-200">
                    <div class="px-6 py-3 border-b border-gray-200">
                        <h4 class="font-medium text-orange-600">Matrix Normalisasi Terbobot</h4>
                    </div>
                    <div class="w-full overflow-x-auto p-1">
                        <table class="w-full divide-y">
                            <thead>
                                <tr>
                                    <th class="sticky left-0 px-4 py-2 text-left text-sm font-medium">Alternatif</th>
                                    @foreach ($topsisResults['subKriterias'] as $subKriteria)
                                        <th class="px-4 py-2 text-center text-sm font-medium min-w-[100px]">
                                            {{ $subKriteria->kode }} ({{ number_format($subKriteria->bobot_global, 2) }}%)
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @foreach ($topsisResults['alternatifs'] as $alternatif)
                                    <tr>
                                        <td class="sticky left-0 px-4 py-2 whitespace-nowrap text-sm font-medium">
                                            {{ $alternatif->kode }} - {{ $alternatif->nama }}
                                        </td>
                                        @foreach ($topsisResults['subKriterias'] as $subKriteria)
                                            <td class="px-4 py-2 text-center text-sm">
                                                {{ number_format($topsisResults['weightedMatrix'][$alternatif->id][$subKriteria->id], 4) }}
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
                
                <!-- Solusi Ideal -->
                @if ($showStep === 'ideal')
                <div class="rounded-lg border border-gray-200">
                    <div class="px-6 py-3 border-b border-gray-200">
                        <h4 class="font-medium text-orange-600">Solusi Ideal Positif (A+) dan Negatif (A-)</h4>
                    </div>
                    <div class="w-full overflow-x-auto p-1">
                        <table class="w-full divide-y">
                            <thead>
                                <tr>
                                    <th class="sticky left-0 px-4 py-2 text-left text-sm font-medium">Solusi Ideal</th>
                                    @foreach ($topsisResults['subKriterias'] as $subKriteria)
                                        <th class="px-4 py-2 text-center text-sm font-medium min-w-[100px]">
                                            {{ $subKriteria->kode }} ({{ $subKriteria->kriteria->tipe }})
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr>
                                    <td class="sticky left-0 px-4 py-2 whitespace-nowrap text-sm font-medium bg-green-50">
                                        A+ (Positif)
                                    </td>
                                    @foreach ($topsisResults['subKriterias'] as $subKriteria)
                                        <td class="px-4 py-2 text-center text-sm bg-green-50">
                                            {{ number_format($topsisResults['idealSolutions']['positive'][$subKriteria->id], 4) }}
                                        </td>
                                    @endforeach
                                </tr>
                                <tr>
                                    <td class="sticky left-0 px-4 py-2 whitespace-nowrap text-sm font-medium bg-red-50">
                                        A- (Negatif)
                                    </td>
                                    @foreach ($topsisResults['subKriterias'] as $subKriteria)
                                        <td class="px-4 py-2 text-center text-sm bg-red-50">
                                            {{ number_format($topsisResults['idealSolutions']['negative'][$subKriteria->id], 4) }}
                                        </td>
                                    @endforeach
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
                
                <!-- Jarak Solusi -->
                @if ($showStep === 'distance')
                <div class="rounded-lg border border-gray-200">
                    <div class="px-6 py-3 border-b border-gray-200">
                        <h4 class="font-medium text-orange-600">Jarak ke Solusi Ideal</h4>
                    </div>
                    <div class="w-full overflow-x-auto p-1">
                        <table class="w-full divide-y">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 text-left text-sm font-medium">Alternatif</th>
                                    <th class="px-4 py-2 text-center text-sm font-medium">D+ (Jarak ke Solusi Positif)</th>
                                    <th class="px-4 py-2 text-center text-sm font-medium">D- (Jarak ke Solusi Negatif)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @foreach ($topsisResults['alternatifs'] as $alternatif)
                                    <tr>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm font-medium">
                                            {{ $alternatif->kode }} - {{ $alternatif->nama }}
                                        </td>
                                        <td class="px-4 py-2 text-center text-sm">
                                            {{ number_format($topsisResults['distances']['positive'][$alternatif->id], 4) }}
                                        </td>
                                        <td class="px-4 py-2 text-center text-sm">
                                            {{ number_format($topsisResults['distances']['negative'][$alternatif->id], 4) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
                
                <!-- Hasil Akhir -->
                @if ($showStep === 'results')
                <div class="rounded-lg border border-gray-200">
                    <div class="px-6 py-3 border-b border-gray-200">
                        <h4 class="font-medium text-orange-600">Nilai Preferensi dan Peringkat</h4>
                    </div>
                    <div class="w-full overflow-x-auto p-1">
                        <table class="w-full divide-y">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 text-center text-sm font-medium">Peringkat</th>
                                    <th class="px-4 py-2 text-left text-sm font-medium">Kode</th>
                                    <th class="px-4 py-2 text-left text-sm font-medium">Nama Alternatif</th>
                                    <th class="px-4 py-2 text-center text-sm font-medium">Nilai Preferensi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @foreach ($topsisResults['results'] as $result)
                                    <tr class="{{ $result['rank'] === 1 ? 'bg-green-50' : '' }}">
                                        <td class="px-4 py-2 text-center text-sm font-bold">
                                            {{ $result['rank'] }}
                                        </td>
                                        <td class="px-4 py-2 text-left text-sm">
                                            {{ $result['kode'] }}
                                        </td>
                                        <td class="px-4 py-2 text-left text-sm">
                                            {{ $result['nama'] }}
                                        </td>
                                        <td class="px-4 py-2 text-center text-sm font-medium">
                                            {{ number_format($result['preference_value'], 4) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        // Data untuk grafik
                        const data = {!! json_encode($topsisResults['results']) !!}.map(result => ({
                            name: result.kode + ' - ' + result.nama,
                            value: result.preference_value,
                            rank: result.rank
                        }));
                        
                        // Buat grafik bar menggunakan ApexCharts
                        const options = {
                            series: [{
                                name: 'Nilai Preferensi',
                                data: data.map(item => parseFloat((item.value * 100).toFixed(2)))
                            }],
                            chart: {
                                type: 'bar',
                                height: 350,
                                toolbar: {
                                    show: true
                                }
                            },
                            plotOptions: {
                                bar: {
                                    horizontal: true,
                                    dataLabels: {
                                        position: 'top'
                                    }
                                }
                            },
                            colors: data.map(item => {
                                return item.rank === 1 ? '#22c55e' : '#3b82f6';
                            }),
                            dataLabels: {
                                enabled: true,
                                formatter: function (val) {
                                    return val.toFixed(2) + '%';
                                },
                                offsetX: 20,
                                style: {
                                    fontSize: '12px',
                                    fontWeight: 'bold'
                                }
                            },
                            xaxis: {
                                categories: data.map(item => item.name),
                                title: {
                                    text: 'Nilai Preferensi (%)'
                                }
                            },
                            yaxis: {
                                title: {
                                    text: 'Alternatif'
                                }
                            },
                            tooltip: {
                                y: {
                                    formatter: function (val) {
                                        return val.toFixed(2) + '%';
                                    }
                                }
                            },
                            title: {
                                text: 'Peringkat Alternatif Berdasarkan Metode TOPSIS',
                                align: 'center'
                            },
                            annotations: {
                                xaxis: [{
                                    x: 0,
                                    strokeDashArray: 0,
                                    borderColor: '#4b5563',
                                    label: {
                                        borderColor: '#4b5563',
                                        style: {
                                            color: '#fff',
                                            background: '#4b5563'
                                        },
                                        text: '0%'
                                    }
                                }]
                            }
                        };

                        const chart = new ApexCharts(document.querySelector("#chart"), options);
                        chart.render();
                    });
                </script>
                @endif
            </div>
        @endif
    </x-filament::section>
    
    <x-filament::section>
        <div class="space-y-4 mt-6">
            <h3 class="text-lg font-medium">Penjelasan Metode TOPSIS</h3>
            <p>
                TOPSIS (Technique for Order of Preference by Similarity to Ideal Solution) adalah metode pengambilan keputusan multi-kriteria 
                yang mengidentifikasi solusi dari alternatif yang ada berdasarkan konsep bahwa alternatif terpilih harus memiliki 
                jarak terpendek dari solusi ideal positif dan jarak terjauh dari solusi ideal negatif.
            </p>
            
            <div class="mt-4">
                <h4 class="font-medium">Langkah-langkah perhitungan TOPSIS:</h4>
                <ol class="list-decimal list-inside mt-2 space-y-2">
                    <li>Membuat matrix keputusan yang ternormalisasi</li>
                    <li>Membuat matrix keputusan ternormalisasi terbobot</li>
                    <li>Menentukan solusi ideal positif dan solusi ideal negatif</li>
                    <li>Menghitung jarak antara nilai setiap alternatif dengan solusi ideal positif dan solusi ideal negatif</li>
                    <li>Menghitung nilai preferensi untuk setiap alternatif</li>
                </ol>
            </div>
            
            <div class="mt-4">
                <h4 class="font-medium">Keterangan:</h4>
                <ul class="list-disc list-inside mt-2 space-y-2">
                    <li>Solusi ideal positif (A+) didefinisikan sebagai jumlah dari nilai terbaik untuk setiap kriteria.</li>
                    <li>Solusi ideal negatif (A-) didefinisikan sebagai jumlah dari nilai terburuk untuk setiap kriteria.</li>
                    <li>Jarak alternatif dari solusi ideal dihitung menggunakan jarak Euclidean.</li>
                    <li>Nilai preferensi (C*) adalah nilai akhir yang digunakan untuk menentukan peringkat alternatif, dengan nilai tertinggi menjadi pilihan terbaik.</li>
                </ul>
            </div>
        </div>
    </x-filament::section>
</x-filament::page>