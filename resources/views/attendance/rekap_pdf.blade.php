<!DOCTYPE html>
<html>
<head>
    <title>Laporan Kinerja Pegawai</title>
    <style>
        body { font-family: sans-serif; font-size: 10pt; }
        
        /* Style khusus untuk tabel header agar tidak ada garis */
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; border-bottom: 3px double black; }
        .header-table td { border: none !important; padding: 5px; vertical-align: middle; }
        .header-table h1 { margin: 0; font-size: 16pt; text-transform: uppercase; }
        .header-table p { margin: 0; font-size: 11pt; }
        
        /* Style Tabel Data */
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid black; padding: 5px; text-align: center; vertical-align: middle; }
        th { background-color: #f2f2f2; font-weight: bold; font-size: 9pt; }
        td { font-size: 9pt; }
        
        .text-left { text-align: left; }
        .text-danger { color: red; font-weight: bold; }
        .ttd-container { width: 100%; margin-top: 50px; overflow: hidden; }
        .ttd-box { float: right; width: 250px; text-align: center; }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td width="15%" style="text-align: center;">
                @php
                    $pathKubar = public_path('img/kubar.png');
                    $base64Kubar = '';
                    if (file_exists($pathKubar)) {
                        $type = pathinfo($pathKubar, PATHINFO_EXTENSION);
                        $data = file_get_contents($pathKubar);
                        $base64Kubar = 'data:image/' . $type . ';base64,' . base64_encode($data);
                    }
                @endphp

                @if($base64Kubar)
                    <img src="{{ $base64Kubar }}" style="height: 85px; width: auto;">
                @else
                    <small style="color:red;">Logo Tidak Ditemukan</small>
                @endif
            </td>
            
            <td width="70%" style="text-align: center;">
                <h1>Pemerintah Kabupaten Kutai Barat</h1>
                <h1>Dinas Pekerjaan Umum dan Penataan Ruang</h1>
                <p>Jl. Komplek Perkantoran III Kabupaten Kutai Barat</p>
                <h1>SENDAWAR</h1>
            </td>

            <td width="15%" style="text-align: center;">
                @php
                    $pathPupr = public_path('img/pupr.png');
                    $base64Pupr = '';
                    if (file_exists($pathPupr)) {
                        $typeP = pathinfo($pathPupr, PATHINFO_EXTENSION);
                        $dataP = file_get_contents($pathPupr);
                        if($typeP == 'jpg') $typeP = 'jpeg';
                        $base64Pupr = 'data:image/' . $typeP . ';base64,' . base64_encode($dataP);
                    }
                @endphp

                @if($base64Pupr)
                    <img src="{{ $base64Pupr }}" style="height: 85px; width: auto;">
                @else
                    <small style="color:red;">Logo PUPR Tidak Ditemukan</small>
                @endif
            </td>
        </tr>
    </table>

    <div style="text-align: center;">
        <h3 style="margin-bottom: 5px;">LAPORAN KINERJA DISIPLIN PEGAWAI</h3>
        <p>Periode: {{ date('F Y', mktime(0,0,0,$bulan, 10)) }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="2" width="5%">NO</th>
                <th rowspan="2" width="30%">NAMA / NIP / JABATAN</th>
                <th rowspan="2" width="10%">JUMLAH<br>HADIR</th>
                
                <th colspan="5" width="25%">RINCIAN KEHADIRAN</th>
                
                <th rowspan="2" width="12%">TOTAL<br>POTONGAN</th>
                <th rowspan="2" width="13%">SKOR<br>KINERJA</th>
            </tr>
            <tr>
                <th width="5%">TLT</th>
                <th width="5%">SKT</th>
                <th width="5%">IZN</th>
                <th width="5%">TK</th>
                <th width="5%" style="background-color: #e0f2fe;">DL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($employees as $index => $e)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td class="text-left" style="padding-left: 8px;">
                    <strong>{{ $e->nama }}</strong><br>
                    <span style="font-size: 8pt;">{{ $e->nip }}</span><br>
                    <span style="font-size: 8pt; font-style: italic;">{{ $e->jabatan }}</span>
                </td>
                
                <td style="font-weight: bold;">{{ $e->total_hadir ?? $e->jml_hadir }} Hari</td>
                
                <td>{{ ($e->jml_telat ?? $e->tlt) > 0 ? ($e->jml_telat ?? $e->tlt) : '-' }}</td>
                <td>{{ ($e->jml_sakit ?? $e->skt) > 0 ? ($e->jml_sakit ?? $e->skt) : '-' }}</td>
                <td>{{ ($e->jml_izin ?? $e->izn) > 0 ? ($e->jml_izin ?? $e->izn) : '-' }}</td>
                <td style="{{ ($e->jml_alpha ?? $e->tk) > 0 ? 'color:red; font-weight:bold;' : '' }}">
                    {{ ($e->jml_alpha ?? $e->tk) > 0 ? ($e->jml_alpha ?? $e->tk) : '-' }}
                </td>
                
                <td style="background-color: #f0f9ff; font-weight: bold;">
                    {{ $e->dl > 0 ? $e->dl : '-' }}
                </td>

                <td class="{{ ($e->rekap_persen ?? $e->total_potongan) > 0 ? 'text-danger' : '' }}">
                    {{ ($e->rekap_persen ?? $e->total_potongan) > 0 ? '-'.($e->rekap_persen ?? $e->total_potongan).'%' : '0%' }}
                </td>
                <td>
                    <span style="font-size: 11pt; font-weight: bold;">{{ $e->skor_kinerja }}%</span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table style="width: 100%; margin-top: 40px; border-collapse: collapse; page-break-inside: avoid;">
        <tr>
            <td style="width: 65%; border: none !important;"></td>
            
            <td style="width: 35%; text-align: center; border: none !important; font-size: 10pt;">
                <p style="margin: 0; padding-bottom: 2px;">Sendawar, {{ date('d F Y') }}</p>
                <p style="margin: 0;">Plt Kepala Sub Bagian Umum & Kepegawaian</p>
                <br><br><br><br><br>
                <p style="margin: 0; text-decoration: underline; font-weight: bold;">DEDE SUDRAJAT, S.T</p>
                <p style="margin: 0;">NIP. 19921006 202012 1 001</p>
            </td>
        </tr>
    </table>

</body>
</html>