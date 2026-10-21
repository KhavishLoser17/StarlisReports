<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderTest extends Model
{
    protected $table = 'order_tests';
    public $timestamps = true;

    protected $fillable = [
        'order_id','test_code','received_at','resulted_at','released_at',
        'resulted_by','verified_by','status'
    ];

    protected $casts = [
        'received_at' => 'datetime',
        'resulted_at' => 'datetime',
        'released_at' => 'datetime',
    ];

    public function order()  { return $this->belongsTo(Order::class); }
    public function test()   { return $this->belongsTo(Test::class, 'test_code', 'test_code'); }
    public function resultBy(){ return $this->belongsTo(UserAccount::class, 'resulted_by', 'ua_user_id'); }
    public function verifyBy(){ return $this->belongsTo(UserAccount::class, 'verified_by', 'ua_user_id'); }
}