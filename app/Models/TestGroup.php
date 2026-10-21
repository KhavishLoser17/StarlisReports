<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestGroup extends Model
{
    protected $table = 'test_groups';
    protected $primaryKey = 'tg_code';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    public function tests()
    {
        return $this->hasMany(Test::class, 'tg_code', 'tg_code');
    }
}