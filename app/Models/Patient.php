<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    protected $table = 'patients';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'patient_no','last_name','first_name','middle_name',
        'birthdate','gender','address','contact_no'
    ];

    public function orders()
    {
        return $this->hasMany(Order::class, 'patient_id');
    }
}