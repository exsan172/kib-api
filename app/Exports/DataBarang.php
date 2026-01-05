<?php

namespace App\Exports;

use App\Models\Barang;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class DataBarang implements FromQuery, WithHeadings, WithChunkReading
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function query()
    {
        return Barang::select(
            'nama_barang',
            'kode_barang',
            'kode_barang_resmi',
            'kondisi',
            'nilai_perolehan',
            'nilai_pertahun',
            'tahun_pembelian',
            'masa_manfaat',
            'keterangan',
            'kategori_barang.nama_kategori AS kategori_barang',
            'lokasi.nama_lokasi AS lokasi',
            'metode_penyusutan.nama_penyusutan AS metode_penyusutan',
            DB::raw("COALESCE(employes.nama, '-') AS employe")
        )
        ->leftJoin('kategori_barang', 'kategori_barang.id', '=', 'barang.kategori_barang_id')
        ->leftJoin('lokasi', 'lokasi.id', '=', 'barang.lokasi_id')
        ->leftJoin('metode_penyusutan', 'metode_penyusutan.id', '=', 'barang.metode_penyusutan_id')
        ->leftJoin('employes', 'employes.id', '=', 'barang.karyawan_id');
    }

    public function chunkSize(): int
    {
        return 1000; // bisa 500 / 1000 / 2000
    }

    public function headings(): array
    {
        return [
            'Nama Barang',         
            'Kode Barang',    
            'kode Barang Resmi',     
            'Kondisi',             
            'Nilai Perolehan',     
            'Nilai Pertahun',      
            'Tahun Pembelian',     
            'Masa Manfaat',        
            'Keterangan',          
            'Kategori Barang',  
            'Lokasi',           
            'Metode Penyusutan',
            "Pemegang"
        ];
    }
}
