<?php

namespace App\Models\RolePermission;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Setting\LocationModel;
use App\Models\RolePermission\PermissionModel;
use App\Models\RolePermission\RoleModel;


class RoleLocationPermissionModel extends Model{

    use HasFactory;

    protected $table = 'role_location_permissions';

    protected $fillable = [
        'role_id', 'location_id', 'permission_id',"status"
    ];

    // Define relationships
    public function user()
    {
        return $this->belongsTo(RoleModel::class);
    }

    public function location()
    {
        return $this->belongsTo(LocationModel::class);
    }

    public function permission()
    {
        return $this->belongsTo(PermissionModel::class);
    }
}
