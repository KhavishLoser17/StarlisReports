<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAccount extends Model
{
    protected $table = 'user_accounts';
    protected $primaryKey = 'ua_user_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $hidden = ['ua_password'];
}