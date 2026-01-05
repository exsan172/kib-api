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

    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {

        $query = Barang::select(
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

        /* ================= FILTER ================= */

        if (!empty($this->filters['search'])) {
            $query->where('nama_barang', 'like', '%' . $this->filters['search'] . '%');
        }

        if (!empty($this->filters['kategori_barang_id'])) {
            $query->where('barang.kategori_barang_id', $this->filters['kategori_barang_id']);
        }

        if (!empty($this->filters['lokasi_id'])) {
            $query->where('barang.lokasi_id', $this->filters['lokasi_id']);
        }

        if (!empty($this->filters['metode_penyusutan_id'])) {
            $query->where('barang.metode_penyusutan_id', $this->filters['metode_penyusutan_id']);
        }

        if (!empty($this->filters['tahun_perolehan'])) {
            $query->where('tahun_pembelian', $this->filters['tahun_perolehan']);
        }

        if (!empty($this->filters['kondisi'])) {
            $query->where('kondisi', $this->filters['kondisi']);
        }

        if (!empty($this->filters['karyawan_id'])) {
            $query->where('barang.karyawan_id', $this->filters['karyawan_id']);
        }

        if (!empty($this->filters['kode_barang'])) {
            $query->where('kode_barang', 'like', '%' . $this->filters['kode_barang'] . '%');
        }

        return $query;
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
