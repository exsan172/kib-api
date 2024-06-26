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
    <center>
        {{-- <img src="{{ asset('images/logo.png') }}" alt="logo"> --}}
        <h1>
            Biro Umum Setda Jabar
        </h1>
        <i>Jl lorem ipsum no 20, rt00</i>
    </center>
    <br/>
    <br/>
    <table>
        <thead>
            <tr>
                <th>Nama Barang</th>
                <th>Kode Barang</th>
                <th>kode Barang Resmi</th>
                <th>Tahun Pembelian</th>
                <th>Kondisi</th>
                <th>Nilai Perolehan</th>
                <th>Kategori Barang</th>
                <th>Lokasi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $item)
            <tr>
                <td style="width:200px">{{ $item["nama_barang"] ?? "-"  }}</td>
                <td style="width:100px">{{ $item["kode_barang"] ?? "-"  }}</td>
                <td style="width:100px">{{ $item["kode_barang_resmi"] ?? "-" }}</td>
                <td style="width:100px">{{ $item["tahun_pembelian"] ?? "-"  }}</td>
                <td style="width:50px">{{ $item["kondisi"] ?? "-"  }}</td>
                <td style="width:90px">{{ $item["nilai_perolehan"] ?? "-"  }}</td>
                <td style="width:100px">{{ $item["kategori"]["nama_kategori"] ?? "-"  }}</td>
                <td style="width:150px">{{ $item["lokasi"]["nama_lokasi"] ?? "-"  }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
