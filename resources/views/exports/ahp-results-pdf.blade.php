<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Hasil Perhitungan AHP</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 13px;
            line-height: 1.6;
            color: #333;
            background-color: #f9f9f9;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background-color: white;
            padding: 25px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            border-radius: 8px;
        }
        h1 {
            font-size: 22px;
            margin-bottom: 15px;
            text-align: center;
            color: #2c3e50;
            padding-bottom: 10px;
            border-bottom: 2px solid #3498db;
        }
        h2 {
            font-size: 16px;
            margin-top: 25px;
            margin-bottom: 15px;
            color: #2980b9;
            padding-bottom: 5px;
            border-bottom: 2px solid #e0e0e0;
        }
        .date-info {
            text-align: center;
            color: #7f8c8d;
            margin-bottom: 20px;
            font-style: italic;
        }
        
        /* Comparison Matrix Styling */
        .comparison-matrix {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .comparison-matrix th {
            background-color: #3498db;
            color: white;
            font-weight: bold;
            padding: 12px;
            text-align: center;
            border: 1px solid #2980b9;
        }
        .comparison-matrix td {
            padding: 10px;
            text-align: center;
            border: 1px solid #e0e0e0;
        }
        .comparison-matrix tr:nth-child(even) {
            background-color: #f5f9fd;
        }
        .comparison-matrix tr:hover {
            background-color: #ebf5fb;
        }
        .comparison-matrix .criteria-name {
            background-color: #2980b9;
            color: white;
            font-weight: bold;
            text-align: left;
            padding-left: 15px;
        }
        .comparison-matrix .diagonal {
            background-color: #e8f4fc;
            font-weight: bold;
        }
        .comparison-matrix .high-value {
            background-color: #ffecb3;
            font-weight: bold;
        }
        .comparison-matrix .low-value {
            background-color: #d5e8ff;
        }
        
        /* Normalized Matrix Styling */
        .normalized-matrix {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .normalized-matrix th {
            background-color: #27ae60;
            color: white;
            font-weight: bold;
            padding: 12px;
            text-align: center;
            border: 1px solid #219653;
        }
        .normalized-matrix td {
            padding: 10px;
            text-align: center;
            border: 1px solid #d5f5e3;
        }
        .normalized-matrix tr:nth-child(even) {
            background-color: #f0fdf4;
        }
        .normalized-matrix tr:hover {
            background-color: #e1f7ec;
        }
        .normalized-matrix .criteria-name {
            background-color: #219653;
            color: white;
            font-weight: bold;
            text-align: left;
            padding-left: 15px;
        }
        .normalized-matrix .high-weight {
            background-color: #c8e6c9;
            font-weight: bold;
        }
        
        /* Priority Vector Styling */
        .priority-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .priority-table th {
            background-color: #9b59b6;
            color: white;
            font-weight: bold;
            padding: 12px;
            text-align: center;
            border: 1px solid #8e44ad;
        }
        .priority-table td {
            padding: 10px;
            border: 1px solid #e8d6f0;
        }
        .priority-table tr:nth-child(even) {
            background-color: #f9f0ff;
        }
        .priority-table tr:hover {
            background-color: #f3e5f5;
        }
        .priority-table .criteria-name {
            text-align: left;
            padding-left: 15px;
            font-weight: bold;
        }
        .priority-table .weight-value {
            font-family: 'Courier New', monospace;
            font-weight: bold;
            color: #2c3e50;
        }
        .priority-table .percentage {
            font-family: 'Courier New', monospace;
            font-weight: bold;
            color: #e74c3c;
        }
        
        /* Consistency Styling */
        .consistency-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .consistency-table th {
            background-color: #e67e22;
            color: white;
            font-weight: bold;
            padding: 12px;
            text-align: center;
            border: 1px solid #d35400;
        }
        .consistency-table td {
            padding: 10px;
            border: 1px solid #fdebd0;
        }
        .consistency-table tr:nth-child(even) {
            background-color: #fef5eb;
        }
        .consistent {
            color: #27ae60;
            font-weight: bold;
        }
        .inconsistent {
            color: #e74c3c;
            font-weight: bold;
        }
        .consistency-value {
            font-family: 'Courier New', monospace;
            font-weight: bold;
        }
        
        /* Warning Box */
        .warning-box {
            margin: 20px 0;
            padding: 15px;
            border-left: 5px solid #e74c3c;
            background-color: #fadbd8;
            border-radius: 4px;
        }
        .warning-box p {
            margin: 0;
            color: #c0392b;
            font-weight: bold;
        }
        
        /* Footer */
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 11px;
            color: #95a5a6;
            padding-top: 15px;
            border-top: 1px solid #ecf0f1;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Hasil Perhitungan AHP Kriteria</h1>
        <div class="date-info">Tanggal: {{ $tanggal }}</div>
        
        <!-- Comparison Matrix -->
        <h2 style="color: #2980b9;">Matriks Perbandingan Berpasangan</h2>
        <table class="comparison-matrix">
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
                        <td class="criteria-name">{{ $kriteria1->nama }}</td>
                        @foreach ($kriterias as $j => $kriteria2)
                            @php
                                $value = $ahpResults['matrix'][$i][$j];
                                $cellClass = '';
                                if ($i == $j) {
                                    $cellClass = 'diagonal';
                                } elseif ($value > 5) {
                                    $cellClass = 'high-value';
                                } elseif ($value < 1) {
                                    $cellClass = 'low-value';
                                }
                            @endphp
                            <td class="{{ $cellClass }}">{{ number_format($value, 3) }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
        
        <!-- Normalized Matrix -->
        <h2 style="color: #27ae60;">Matriks Ternormalisasi</h2>
        <table class="normalized-matrix">
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
                        <td class="criteria-name">{{ $kriteria1->nama }}</td>
                        @foreach ($kriterias as $j => $kriteria2)
                            @php
                                $value = $ahpResults['normalized_matrix'][$i][$j];
                                $cellClass = $value > 0.3 ? 'high-weight' : '';
                            @endphp
                            <td class="{{ $cellClass }}">{{ number_format($value, 3) }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
        
        <!-- Priority Vector -->
        <h2 style="color: #9b59b6;">Bobot Kriteria (Priority Vector)</h2>
        <table class="priority-table">
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
                        <td class="criteria-name">{{ $kriteria->nama }}</td>
                        <td class="weight-value">{{ number_format($ahpResults['priority_vector'][$i], 4) }}</td>
                        <td class="percentage">{{ number_format($ahpResults['priority_vector'][$i] * 100, 2) }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        
        <!-- Consistency -->
        <h2 style="color: #e67e22;">Konsistensi Penilaian</h2>
        <table class="consistency-table">
            <tr>
                <th>Parameter</th>
                <th>Nilai</th>
            </tr>
            <tr>
                <td class="criteria-name">Lambda Max</td>
                <td class="consistency-value">{{ number_format($ahpResults['lambda_max'], 4) }}</td>
            </tr>
            <tr>
                <td class="criteria-name">Consistency Index (CI)</td>
                <td class="consistency-value">{{ number_format($ahpResults['consistency_index'], 4) }}</td>
            </tr>
            <tr>
                <td class="criteria-name">Consistency Ratio (CR)</td>
                <td class="consistency-value {{ $ahpResults['is_consistent'] ? 'consistent' : 'inconsistent' }}">
                    {{ number_format($ahpResults['consistency_ratio'], 4) }}
                    ({{ $ahpResults['is_consistent'] ? 'Konsisten' : 'Tidak Konsisten' }})
                </td>
            </tr>
        </table>
        
        <!-- Warning Message -->
        @if (!$ahpResults['is_consistent'])
            <div class="warning-box">
                <p>⚠️ <strong>Peringatan:</strong> Nilai CR > 0.1 menunjukkan ketidakkonsistenan. Mohon lakukan perbandingan ulang.</p>
            </div>
        @endif
        
        <div class="footer">
            <p>Dokumen ini digenerate oleh sistem pada {{ $tanggal }}</p>
        </div>
    </div>
</body>
</html>