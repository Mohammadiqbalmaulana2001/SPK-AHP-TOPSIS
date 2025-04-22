<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
        }
        .page-header {
            text-align: center;
            margin-bottom: 20px;
            padding: 15px;
            background-color: #DCE6F1;
            border-bottom: 2px solid #4472C4;
        }
        h1 {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
            color: #1F497D;
        }
        h2 {
            font-size: 14px;
            font-weight: bold;
            margin-top: 25px;
            margin-bottom: 10px;
            color: #1F497D;
            padding-bottom: 5px;
            border-bottom: 2px solid #4472C4;
        }
        .kriteria-info {
            margin-bottom: 15px;
        }
        .kriteria-info p {
            margin: 5px 0;
            font-weight: bold;
        }
        
        /* Special styling for comparison matrix */
        .comparison-matrix {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .comparison-matrix th {
            background-color: #2F5496;
            color: white;
            font-weight: bold;
            padding: 10px;
            border: 1px solid #1F3864;
        }
        .comparison-matrix td {
            padding: 8px;
            text-align: center;
            border: 1px solid #95B3D7;
        }
        .comparison-matrix tr:nth-child(even) {
            background-color: #EAF1FD;
        }
        .comparison-matrix tr:hover {
            background-color: #D5E0F3;
        }
        .comparison-matrix .diagonal-cell {
            background-color: #DBE5F1;
            font-weight: bold;
        }
        .comparison-matrix .high-value {
            background-color: #FCD5B4;
            font-weight: bold;
        }
        .comparison-matrix .low-value {
            background-color: #DAE8FC;
        }
        
        /* Styling for normalized matrix */
        .normalized-matrix {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .normalized-matrix th {
            background-color: #548235;
            color: white;
            font-weight: bold;
            padding: 10px;
            border: 1px solid #385723;
        }
        .normalized-matrix td {
            padding: 8px;
            text-align: center;
            border: 1px solid #A9D18E;
        }
        .normalized-matrix tr:nth-child(even) {
            background-color: #EBF1DE;
        }
        .normalized-matrix tr:hover {
            background-color: #D8E4BC;
        }
        .normalized-matrix .high-weight {
            background-color: #C5E0B4;
            font-weight: bold;
        }
        .normalized-matrix .low-weight {
            background-color: #E2EFDA;
        }
        
        /* Other tables */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 8px;
            text-align: center;
            border: 1px solid #B4C6E7;
        }
        th {
            background-color: #4472C4;
            color: white;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #F2F2F2;
        }
        tr:hover {
            background-color: #E6E6E6;
        }
        
        .matrix-title {
            font-weight: bold;
            margin-top: 15px;
            margin-bottom: 5px;
        }
        .consistent {
            color: #00B050;
            font-weight: bold;
        }
        .not-consistent {
            color: #FF0000;
            font-weight: bold;
        }
        .consistency-grid {
            width: 100%;
            margin-top: 15px;
            margin-bottom: 20px;
        }
        .consistency-grid td {
            width: 33%;
            padding: 10px;
            vertical-align: middle;
            background-color: #F8F8F8;
            border: 1px solid #D9D9D9;
        }
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #D9D9D9;
        }
        .left-align {
            text-align: left;
            font-weight: bold;
        }
        .numeric-value {
            font-family: 'Courier New', monospace;
        }
        .warning-box {
            background-color: #FFF2CC;
            border-left: 4px solid #FFD966;
            padding: 10px;
            margin: 15px 0;
        }
        .highlight {
            background-color: #E2EFDA;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="page-header">
        <h1>{{ $title }}</h1>
        <div class="kriteria-info">
            <p><strong>Kriteria:</strong> <span style="color: #1F497D;">{{ $kriteria->nama }}</span></p>
            <p><strong>Tanggal:</strong> {{ $date }}</p>
        </div>
    </div>
    
    <!-- Matriks Perbandingan Berpasangan -->
    <h2 style="color: #2F5496;">Matriks Perbandingan Berpasangan</h2>
    <table class="comparison-matrix">
        <thead>
            <tr>
                <th>Subkriteria</th>
                @foreach ($subKriterias as $subkriteria)
                    <th>{{ $subkriteria->nama }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($subKriterias as $i => $subkriteria1)
                <tr>
                    <td class="left-align" style="background-color: #2F5496; color: white;">{{ $subkriteria1->nama }}</td>
                    @foreach ($subKriterias as $j => $subkriteria2)
                        @php
                            $value = $ahpResults['matrix'][$i][$j];
                            $cellClass = '';
                            if ($i == $j) {
                                $cellClass = 'diagonal-cell';
                            } elseif ($value > 5) {
                                $cellClass = 'high-value';
                            } elseif ($value < 1 && $value != 0) {
                                $cellClass = 'low-value';
                            }
                        @endphp
                        <td class="numeric-value {{ $cellClass }}">{{ number_format($value, 3) }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
    
    <!-- Matriks Ternormalisasi -->
    <h2 style="color: #548235;">Matriks Ternormalisasi</h2>
    <table class="normalized-matrix">
        <thead>
            <tr>
                <th>Subkriteria</th>
                @foreach ($subKriterias as $subkriteria)
                    <th>{{ $subkriteria->nama }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($subKriterias as $i => $subkriteria1)
                <tr>
                    <td class="left-align" style="background-color: #548235; color: white;">{{ $subkriteria1->nama }}</td>
                    @foreach ($subKriterias as $j => $subkriteria2)
                        @php
                            $value = $ahpResults['normalized_matrix'][$i][$j];
                            $cellClass = $value > 0.5 ? 'high-weight' : 'low-weight';
                        @endphp
                        <td class="numeric-value {{ $cellClass }}">{{ number_format($value, 3) }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
    
    <!-- Bobot Subkriteria -->
    <h2>Bobot Subkriteria (Priority Vector)</h2>
    <table>
        <thead>
            <tr>
                <th>Subkriteria</th>
                <th>Bobot Lokal</th>
                <th>% Lokal</th>
                <th>Bobot Global</th>
                <th>% Global</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($subKriterias as $i => $subkriteria)
                <tr>
                    <td class="left-align">{{ $subkriteria->nama }}</td>
                    <td class="numeric-value highlight">{{ number_format($ahpResults['priority_vector'][$i], 4) }}</td>
                    <td class="numeric-value highlight">{{ number_format($ahpResults['priority_vector'][$i] * 100, 2) }}%</td>
                    <td class="numeric-value highlight">{{ number_format($ahpResults['global_weights'][$subkriteria->id], 4) }}</td>
                    <td class="numeric-value highlight">{{ number_format($ahpResults['global_weights'][$subkriteria->id] * 100, 2) }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    
    <!-- Konsistensi Penilaian -->
    <h2>Konsistensi Penilaian</h2>
    <table class="consistency-grid">
        <tr>
            <td>
                <strong>Lambda Max:</strong><br>
                <span class="numeric-value">{{ number_format($ahpResults['lambda_max'], 4) }}</span>
            </td>
            <td>
                <strong>Consistency Index (CI):</strong><br>
                <span class="numeric-value">{{ number_format($ahpResults['consistency_index'], 4) }}</span>
            </td>
            <td>
                <strong>Consistency Ratio (CR):</strong><br>
                <span class="numeric-value">{{ number_format($ahpResults['consistency_ratio'], 4) }}</span>
                <span class="{{ $ahpResults['is_consistent'] ? 'consistent' : 'not-consistent' }}">
                    ({{ $ahpResults['is_consistent'] ? 'Konsisten' : 'Tidak Konsisten' }})
                </span>
            </td>
        </tr>
    </table>
    
    @if (!$ahpResults['is_consistent'])
        <div class="warning-box">
            <p class="not-consistent">
                <strong>Peringatan:</strong> Nilai CR > 0.1 menunjukkan ketidakkonsistenan. Mohon lakukan perbandingan ulang.
            </p>
        </div>
    @endif
    
    <div class="footer">
        <p>Dicetak oleh sistem pada: {{ $date }}</p>
    </div>
</body>
</html>