<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Test extends Model
{
    protected $table = 'tests';
    protected $primaryKey = 'test_code';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = true;

    public function testGroup()
    {
        return $this->belongsTo(TestGroup::class, 'tg_code', 'tg_code');
    }
}