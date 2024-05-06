<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dosen extends Model
{
    use HasFactory;
    protected $primaryKey = 'Dosen_Id';
    protected $keyType = 'integer';
    protected $table = 'dosen';
    public $timestamps = false;
    protected $fillable = [
        'Dosen_Nama',
        'Dosen_Email',
        'Dosen_Password'
    ];
}
