<?php

namespace App\Models\RolePermission;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\RolePermission\PermissionModel;
use App\Models\RolePermission\RoleModel;

class RolePermissionModel extends Model
{
    use HasFactory;

    protected $table = 'role_permissions';

    protected $fillable = ([
        "id",
        'role_id',
        'permission_id',
        'status'
    ]);

    public function role()
    {
        return $this->belongsTo(RoleModel::class);
    }

    public function permission()
    {
        return $this->belongsTo(PermissionModel::class);
    }

}
