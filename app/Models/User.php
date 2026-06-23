<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * @property int $id_user
 * @property int|null $id_dosen
 * @property int|null $id_lab
 * @property string $username
 * @property string $role
 */
class User extends Authenticatable
{
    protected $primaryKey = 'id_user';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'id_dosen',
        'id_lab',
        'username',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
}
