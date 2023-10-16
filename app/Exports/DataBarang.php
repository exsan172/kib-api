<?php

namespace App\Exports;

use App\Models\Barang;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DataBarang implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
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
            'metode_penyusutan.nama_penyusutan AS metode_penyusutan'
        )
        ->leftJoin('kategori_barang', 'kategori_barang.id', '=', 'barang.kategori_barang_id')
        ->leftJoin('lokasi', 'lokasi.id', '=', 'barang.lokasi_id')
        ->leftJoin('metode_penyusutan', 'metode_penyusutan.id', '=', 'barang.metode_penyusutan_id')
        ->get();
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
            'Metode Penyusutan' 
        ];
    }
}
