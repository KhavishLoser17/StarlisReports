<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';
    public $timestamps = true;

    protected $fillable = [
        'order_no','patient_id','pt_code','st_id','ua_user_id',
        'received_at','released_at','status','remarks'
    ];

    protected $casts = [
        'received_at' => 'datetime',
        'released_at' => 'datetime',
    ];

    public function patient()    { return $this->belongsTo(Patient::class); }
    public function patientType(){ return $this->belongsTo(PatientType::class, 'pt_code', 'pt_code'); }
    public function source()     { return $this->belongsTo(Source::class, 'st_id', 'st_id'); }
    public function user()       { return $this->belongsTo(UserAccount::class, 'ua_user_id', 'ua_user_id'); }
    public function orderTests() { return $this->hasMany(OrderTest::class); }
}