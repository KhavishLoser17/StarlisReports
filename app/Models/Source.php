<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Source extends Model
{
    protected $table = 'source_table';
    protected $primaryKey = 'st_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
}