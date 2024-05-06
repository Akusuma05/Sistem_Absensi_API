<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kelas_Mahasiswa extends Model
{
    use HasFactory;
    protected $primaryKey = 'Kelas_Mahasiswa_Id';
    protected $keyType = 'integer';
    protected $table = 'kelas_mahasiswa';
    public $timestamps = false;
    protected $fillable = [
        'Kelas_Id',
        'Mahasiswa_Id'
    ];

    public function absensi(){
        return $this->hasMany(Absensi::class, 'Kelas_Mahasiswa_Id', 'Kelas_Mahasiswa_Id');
    }

    public function kelas(){
        return $this->belongsTo(Kelas::class, 'Kelas_Id', 'Kelas_Id');
    }

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'Mahasiswa_Id', 'Mahasiswa_Id');
    }
}
