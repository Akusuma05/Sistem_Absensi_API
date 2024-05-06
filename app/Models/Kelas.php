<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    use HasFactory;
    protected $primaryKey = 'Kelas_Id';
    protected $keyType = 'integer';
    protected $table = 'kelas';
    public $timestamps = false;
    protected $fillable = [
        'Kelas_Nama',
        'Kelas_Lokasi'
    ];

    public function Kelas_Mahasiswa(){
        return $this->hasMany(Kelas_Mahasiswa::class, 'Kelas_Id', 'Kelas_Id');
    }
}
