<?php

namespace App\Models\RolePermission;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\RolePermission\RoleModel;
use App\Models\User;

class PermissionModel extends Model
{
    use HasFactory;
    protected $table = 'permissions';
    protected $fillable = [
        'id',
        'permission_name',
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permissions', 'permission_id', 'role_id');
    }

    public function users()
    {
        return $this->hasManyThrough(
            User::class,
            RolePermissionModel::class,
            'permission_id', // Foreign key on role_permissions table
            'id', // Foreign key on users table
            'id', // Local key on permissions table
            'user_id' // Local key on role_permissions table
        );
    }
}
