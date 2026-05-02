<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Data Pegawai Non-ASN</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 9pt;
        color: #1a1a2e;
        background: #fff;
    }
    .header-wrapper {
        text-align: center;
        border-bottom: 3px double #c00;
        padding-bottom: 8px;
        margin-bottom: 12px;
    }
    .header-title {
        font-size: 13pt;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #c00;
    }
    .header-subtitle {
        font-size: 8pt;
        color: #555;
        margin-top: 2px;
    }
    .meta-info {
        font-size: 7.5pt;
        color: #777;
        text-align: right;
        margin-bottom: 8px;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 4px;
    }
    thead tr {
        background-color: #c00;
        color: #fff;
    }
    thead th {
        padding: 5px 6px;
        text-align: center;
        font-size: 7.5pt;
        font-weight: bold;
        text-transform: uppercase;
        border: 1px solid #a00;
    }
    tbody tr:nth-child(even) {
        background-color: #fdf5f5;
    }
    tbody tr:nth-child(odd) {
        background-color: #fff;
    }
    tbody td {
        padding: 4px 6px;
        border: 1px solid #ddd;
        font-size: 8pt;
        vertical-align: top;
    }
    .col-no     { width: 4%;  text-align: center; }
    .col-nama   { width: 22%; }
    .col-jk     { width: 8%;  text-align: center; }
    .col-pend   { width: 10%; text-align: center; }
    .col-prodi  { width: 18%; }
    .col-umur   { width: 7%;  text-align: center; }
    .col-satker { width: 17%; }
    .col-sub    { width: 14%; }
    .footer {
        margin-top: 14px;
        font-size: 7.5pt;
        color: #888;
        text-align: center;
        border-top: 1px solid #ddd;
        padding-top: 6px;
    }
    .badge-k2 {
        display: inline-block;
        background: #dc3545;
        color: #fff;
        padding: 1px 4px;
        border-radius: 3px;
        font-size: 6.5pt;
        font-weight: bold;
    }
    .text-muted { color: #999; }
</style>
</head>
<body>

<div class="header-wrapper">
    <div class="header-title">Rekapitulasi Data Pegawai Non-ASN</div>
    <div class="header-subtitle">Dinas Kesehatan Provinsi Lampung</div>
</div>

<div class="meta-info">
    Tanggal cetak: {{ \Carbon\Carbon::now()->translatedFormat('d F Y, H:i') }} WIB &nbsp;|&nbsp;
    Total: <strong>{{ count($pegawais) }}</strong> pegawai
    @if(!empty($filterInfo))
        &nbsp;|&nbsp; Filter: {{ $filterInfo }}
    @endif
</div>

<table>
    <thead>
        <tr>
            <th class="col-no">No</th>
            <th class="col-nama">Nama</th>
            <th class="col-jk">J.K</th>
            <th class="col-pend">Pendidikan</th>
            <th class="col-prodi">Prodi/Jurusan</th>
            <th class="col-umur">Umur</th>
            <th class="col-satker">Satker/Satwil</th>
            <th class="col-sub">Sub/Bag</th>
        </tr>
    </thead>
    <tbody>
        @forelse($pegawais as $i => $pegawai)
        @php
            $umur = $pegawai->tgl_lahir ? \Carbon\Carbon::parse($pegawai->tgl_lahir)->age : '-';
            $isInduk = optional($pegawai->satker)->level === 'induk' || empty(optional($pegawai->satker)->parent_id);
            $satkerInduk = $isInduk
                ? strtoupper(optional($pegawai->satker)->nama_satker ?? '-')
                : strtoupper(optional(optional($pegawai->satker)->parent)->nama_satker ?? '-');
            $subBag = $isInduk ? '-' : strtoupper(optional($pegawai->satker)->nama_satker ?? '-');
            $jk = $pegawai->jenis_kelamin === 'Pria' ? 'L' : 'P';
        @endphp
        <tr>
            <td class="col-no">{{ $i + 1 }}</td>
            <td class="col-nama">
                {{ strtoupper($pegawai->nama) }}
                @if($pegawai->status_k2 === 'K-II')
                    <span class="badge-k2">K-II</span>
                @endif
            </td>
            <td class="col-jk">{{ $jk }}</td>
            <td class="col-pend">{{ strtoupper($pegawai->pendidikan ?? '-') }}</td>
            <td class="col-prodi">{{ strtoupper(optional($pegawai->prodi)->nama ?? '-') }}</td>
            <td class="col-umur">{{ is_numeric($umur) ? $umur . ' Th' : '-' }}</td>
            <td class="col-satker">{{ $satkerInduk }}</td>
            <td class="col-sub">{{ $subBag }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="8" style="text-align:center; color:#999; padding:16px;">
                Tidak ada data pegawai.
            </td>
        </tr>
        @endforelse
    </tbody>
</table>

<div class="footer">
    Dokumen ini dicetak otomatis oleh Sistem Informasi Pegawai &mdash; Dinas Kesehatan Provinsi Lampung
</div>

</body>
</html>
