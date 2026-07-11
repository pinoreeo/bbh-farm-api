<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected function perPage(Request $request, int $default = 15, int $max = 100): int
    {
        return max(1, min((int) $request->query('per_page', $default), $max));
    }

    /**
     * @param  array<string, mixed>  $rules
     * @return array<string, mixed>
     */
    protected function validated(Request $request, array $rules): array
    {
        return $request->validate($rules, [
            'required' => 'Kolom :attribute wajib diisi.',
            'required_without' => 'Kolom :attribute wajib diisi jika :values belum diisi.',
            'integer' => 'Kolom :attribute harus berisi pilihan yang valid.',
            'numeric' => 'Kolom :attribute harus berupa angka.',
            'boolean' => 'Kolom :attribute harus dipilih Ya atau Tidak.',
            'date' => 'Kolom :attribute harus berisi tanggal yang valid.',
            'date_format' => 'Format :attribute belum valid.',
            'exists' => 'Pilihan :attribute tidak valid atau datanya sudah tidak tersedia.',
            'unique' => ':attribute sudah digunakan.',
            'in' => 'Pilihan :attribute tidak valid.',
            'min' => 'Nilai :attribute terlalu kecil.',
            'max' => 'Nilai :attribute terlalu panjang.',
            'before_or_equal' => ':attribute tidak boleh melewati tanggal hari ini.',
        ], $this->validationAttributes());
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'tag_number' => 'Tag Kambing',
            'breed_id' => 'Ras Kambing',
            'sex' => 'Jenis Kelamin',
            'generation' => 'Generasi',
            'birth_date' => 'Tanggal Lahir',
            'birth_place' => 'Tempat Lahir',
            'life_status' => 'Status Hidup',
            'is_impor' => 'Kambing Impor',
            'animal_id' => 'Kambing',
            'record_date' => 'Tanggal Timbang',
            'weight_kg' => 'Berat',
            'pen_code' => 'Kode Kandang',
            'colony_type' => 'Jenis Kandang',
            'location' => 'Lokasi Kandang',
            'capacity' => 'Kapasitas',
            'period_code' => 'Kode Periode',
            'colony_pen_id' => 'Kode Kandang',
            'start_date' => 'Tanggal Mulai',
            'end_date' => 'Tanggal Selesai',
            'male_animal_id' => 'Tag Pejantan',
            'female_animal_id' => 'Tag Betina',
            'female_animal_ids' => 'Tag Betina',
            'breeding_period_id' => 'Kode Periode',
            'breeding_female_id' => 'Betina Dalam Periode',
            'entry_date' => 'Tanggal Masuk',
            'exit_date' => 'Tanggal Keluar',
            'exit_reason' => 'Alasan Keluar',
            'check_date' => 'Tanggal Periksa',
            'is_pregnant' => 'Status Bunting',
            'method' => 'Metode Periksa',
            'estimated_gestation_days' => 'Estimasi Usia Kebuntingan',
            'dam_id' => 'Tag Induk',
            'sire_id' => 'Tag Pejantan',
            'birth_time' => 'Jam Lahir',
            'offspring_count' => 'Jumlah Anak',
            'birth_process' => 'Proses Kelahiran',
            'dam_grade' => 'Grade Induk',
            'birth_event_id' => 'Data Kelahiran',
            'offspring_birth_id' => 'Data Cempe Lahir',
            'offspring_animal_id' => 'Tag Kambing',
            'birth_weight_kg' => 'Berat Lahir',
            'birth_status' => 'Status Hidup Cempe',
            'target_animal_id' => 'Tag Cempe Lahir',
            'care_date' => 'Tanggal Perawatan',
            'administration_method' => 'Metode Pemberian',
            'volume_ml' => 'Volume',
            'navel_iodine_status' => 'Iodin Pusar',
            'vitamin_ade_ml' => 'Vitamin ADE',
            'vitamin_b_complex_ml' => 'Vitamin B-Complex',
            'intracin_ml' => 'Intracin',
            'treatment_date' => 'Tanggal Perawatan',
            'treatment_group' => 'Jenis Perawatan',
            'product_name' => 'Nama Produk',
            'dosage' => 'Dosis',
            'administration_route' => 'Cara Pemberian',
            'action_category' => 'Kategori Tindakan',
            'category_name' => 'Jenis Vaksin',
            'vaccination_date' => 'Tanggal Vaksin',
            'certificate_type_id' => 'Jenis Sertifikat',
            'issue_place' => 'Tempat Terbit',
            'death_date' => 'Tanggal Kematian',
            'death_time' => 'Waktu Kematian',
            'cause_of_death' => 'Penyebab Kematian',
            'key_identifier' => 'Key Identifier',
            'public_key_pem' => 'Public Key',
            'key_length' => 'Panjang Kunci',
            'notes' => 'Catatan',
            'status' => 'Status',
        ];
    }
}
