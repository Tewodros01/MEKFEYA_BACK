<?php

namespace App\Models\RolePermission;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\RolePermission\PermissionModel;
use App\Models\RolePermission\RoleLocationPermissionModel;
use App\Models\User;

class RoleModel extends Model
{
    use HasFactory;

    protected $table = 'roles';
    protected $fillable = [
        'role_name',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }


    public function roleLocationPermissions()
    {
        return $this->hasMany(
            RoleLocationPermissionModel::class,
            'role_id', // Foreign key on role_location_permissions table
            'id' // Local key on role_permissions table
        );
    }

    public function permissions()
    {
        return $this->hasManyThrough(
            PermissionModel::class,
            RolePermissionModel::class,
            'role_id', // Foreign key on role_permissions table
            'id', // Foreign key on permissions table
            'id', // Local key on roles table
            'permission_id' // Local key on role_permissions table
        );
    }

}
