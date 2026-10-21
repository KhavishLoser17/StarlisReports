<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientType extends Model
{
    protected $table = 'patient_types';
    protected $primaryKey = 'pt_code';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;   // existing table has no timestamps
}