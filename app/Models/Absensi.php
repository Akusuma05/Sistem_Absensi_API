<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    use HasFactory;
    protected $primaryKey = 'Absensi_Id';
    protected $keyType = 'integer';
    protected $table = 'absensi';
    public $timestamps = false;
    protected $fillable = [
        'Kelas_Mahasiswa_Id',
        'Absensi_Waktu'
    ];

    public function Kelas_Mahasiswa(){
        return $this->belongsTo(Kelas_Mahasiswa::class, 'Kelas_Mahasiswa_Id', 'Kelas_Mahasiswa_Id');
    }
}
