<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mahasiswa extends Model
{
    use HasFactory;
    protected $primaryKey = 'Mahasiswa_Id';
    protected $keyType = 'integer';
    protected $table = 'mahasiswa';
    public $timestamps = false;
    protected $fillable = [
        'Mahasiswa_Nama',
        'Mahasiswa_Foto'
    ];

    public function Kelas_Mahasiswa(){
        return $this->hasMany(Kelas_Mahasiswa::class, 'Mahasiswa_Id', 'Mahasiswa_Id');
    }
}
