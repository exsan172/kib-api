<!DOCTYPE html>
<html>
<head>
    <title>Tabel PDF</title>
    <style>

        table {
            border-collapse: collapse;
            font-size: xx-small
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
        }
    </style>
</head>
<body>
    <h1>Data Barang</h1>
    <table>
        <thead>
            <tr>
                <th>Nama Barang</th>
                <th>Kode Barang</th>
                <th>kode Barang Resmi</th>
                <th>Tahun Perolehan</th>
                <th>Kondisi</th>
                <th>Nilai Perolehan</th>
                {{-- <th>Nilai Pertahun</th> --}}
                {{-- <th>Tahun Pembelian</th> --}}
                {{-- <th>Masa Manfaat</th> --}}
                {{-- <th>Keterangan</th> --}}
                <th>Kategori Barang</th>
                <th>Lokasi</th>
                {{-- <th>Metode Penyusutan</th> --}}
            </tr>
        </thead>
        <tbody>
            @foreach($data as $item)
            <tr>
                <td style="width:100px">{{ $item["nama_barang"] ?? "-"  }}</td>
                <td>{{ $item["kode_barang"] ?? "-"  }}</td>
                <td>{{ $item["kode_barang_resmi"] ?? "-" }}</td>
                <td>{{ $item["tahun_perolehan"] ?? "-"  }}</td>
                <td>{{ $item["kondisi"] ?? "-"  }}</td>
                <td>{{ $item["nilai_perolehan"] ?? "-"  }}</td>
                {{-- <td>{{ $item["nilai_pertahun"] ?? "-"  }}</td> --}}
                {{-- <td>{{ $item["tahun_pembelian"] ?? "-"  }}</td> --}}
                {{-- <td>{{ $item["masa_manfaat"] ?? "-"  }}</td> --}}
                {{-- <td>{{ $item["keterangan"] ?? "-" }}</td> --}}
                <td>{{ $item["kategori"]["nama_kategori"] ?? "-"  }}</td>
                <td style="width:100px">{{ $item["lokasi"]["nama_lokasi"] ?? "-"  }}</td>
                {{-- <td>{{ $item["metode_penyusutan"]["nama_penyusutan"] ?? "-"  }}</td> --}}
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>