<x-filament::page>
    <div class="space-y-6">
        <div>
            <h2 class="text-2xl font-semibold tracking-tight text-gray-800 dark:text-white">
                Hasil Perhitungan Metode TOPSIS
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Berikut adalah hasil perhitungan nilai preferensi dan peringkat alternatif berdasarkan metode TOPSIS.
            </p>
        </div>

        <div class="overflow-x-auto bg-white shadow-md rounded-lg border dark:bg-gray-900 dark:border-gray-700">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-100 dark:bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                            Peringkat
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                            Nama Alternatif
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                            D<sup>+</sup> (Solusi Ideal Positif)
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                            D<sup>-</sup> (Solusi Ideal Negatif)
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                            Nilai Preferensi (V<sub>i</sub>)
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-800">
                    @foreach($results as $index => $result)
                        <tr class="@if($index === 0) bg-green-50 dark:bg-green-900/20 @endif">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-gray-100 font-semibold">
                                {{ $index + 1 }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-gray-100">
                                {{ $result['nama'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                {{ number_format($result['d_plus'], 4) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                {{ number_format($result['d_minus'], 4) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-indigo-600 dark:text-indigo-400">
                                {{ number_format($result['nilai_preferensi'], 4) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-filament::page>
