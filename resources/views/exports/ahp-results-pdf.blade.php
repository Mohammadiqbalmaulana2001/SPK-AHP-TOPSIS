<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Hasil Perhitungan AHP</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
        }
        .container {
            width: 100%;
            padding: 10px;
        }
        h1 {
            font-size: 18px;
            margin-bottom: 10px;
            text-align: center;
        }
        h2 {
            font-size: 14px;
            margin-top: 20px;
            margin-bottom: 10px;
            color: #333;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .text-left {
            text-align: left;
        }
        .consistency-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .consistency-box {
            border: 1px solid #ddd;
            padding: 10px;
            width: 30%;
            text-align: center;
        }
        .consistency-label {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .consistency-value {
            font-size: 16px;
        }
        .consistent {
            color: green;
        }
        .inconsistent {
            color: red;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Hasil Perhitungan Analytic Hierarchy Process (AHP)</h1>
        <p>Tanggal: {{ $tanggal }}</p>
        
        <!-- Matriks Perbandingan -->
        <h2>Matriks Perbandingan Berpasangan</h2>
        <table>
            <thead>
                <tr>
                    <th>Kriteria</th>
                    @foreach ($kriterias as $kriteria)
                        <th>{{ $kriteria->nama }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($kriterias as $i => $kriteria1)
                    <tr>
                        <td class="text-left">{{ $kriteria1->nama }}</td>
                        @foreach ($kriterias as $j => $kriteria2)
                            <td>{{ number_format($ahpResults['matrix'][$i][$j], 3) }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
        
        <!-- Matriks Ternormalisasi -->
        <h2>Matriks Ternormalisasi</h2>
        <table>
            <thead>
                <tr>
                    <th>Kriteria</th>
                    @foreach ($kriterias as $kriteria)
                        <th>{{ $kriteria->nama }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($kriterias as $i => $kriteria1)
                    <tr>
                        <td class="text-left">{{ $kriteria1->nama }}</td>
                        @foreach ($kriterias as $j => $kriteria2)
                            <td>{{ number_format($ahpResults['normalized_matrix'][$i][$j], 3) }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
        
        <!-- Bobot Kriteria -->
        <h2>Bobot Kriteria (Priority Vector)</h2>
        <table>
            <thead>
                <tr>
                    <th>Kriteria</th>
                    <th>Bobot</th>
                    <th>Persentase</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($kriterias as $i => $kriteria)
                    <tr>
                        <td class="text-left">{{ $kriteria->nama }}</td>
                        <td>{{ number_format($ahpResults['priority_vector'][$i], 4) }}</td>
                        <td>{{ number_format($ahpResults['priority_vector'][$i] * 100, 2) }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        
        <!-- Konsistensi -->
        <h2>Konsistensi Penilaian</h2>
        <table>
            <tr>
                <th>Parameter</th>
                <th>Nilai</th>
            </tr>
            <tr>
                <td class="text-left">Lambda Max</td>
                <td>{{ number_format($ahpResults['lambda_max'], 4) }}</td>
            </tr>
            <tr>
                <td class="text-left">Consistency Index (CI)</td>
                <td>{{ number_format($ahpResults['consistency_index'], 4) }}</td>
            </tr>
            <tr>
                <td class="text-left">Consistency Ratio (CR)</td>
                <td class="{{ $ahpResults['is_consistent'] ? 'consistent' : 'inconsistent' }}">
                    {{ number_format($ahpResults['consistency_ratio'], 4) }}
                    ({{ $ahpResults['is_consistent'] ? 'Konsisten' : 'Tidak Konsisten' }})
                </td>
            </tr>
        </table>
        
        <!-- Keterangan tambahan -->
        @if (!$ahpResults['is_consistent'])
            <div style="margin-top: 10px; padding: 10px; border: 1px solid #f8d7da; background-color: #f8d7da; color: #721c24;">
                <p><strong>Peringatan:</strong> Nilai CR > 0.1 menunjukkan ketidakkonsistenan. Mohon lakukan perbandingan ulang.</p>
            </div>
        @endif
        
        <div class="footer">
            <p>Dokumen ini digenerate oleh sistem pada {{ $tanggal }}</p>
        </div>
    </div>
</body>
</html>