<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use App\Models\RolePermission\RoleModel;
use App\Models\RolePermission\PermissionModel;
use App\Models\RolePermission\RolePermissionModel;
use App\Models\Setting\LocationModel;
use App\Models\RolePermission\RoleLocationPermissionModel;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $table = 'users';
    protected $fillable = [
        'first_name',
        'last_name',
        'gender',
        'email',
        'phone_number',
        'password',
        'location_id', // Include this line
        'image_path',
        'imagename',
        'email_verified_at',
        'last_login_at',
        'last_login_ip',
        'role_id',
        'active',
        'theme_setting',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function role()
    {
        return $this->belongsTo(RoleModel::class);
    }

    public function location()
    {
        return $this->belongsTo(LocationModel::class);
    }

    public function permissions()
    {
        return $this->hasManyThrough(
            PermissionModel::class,
            RolePermissionModel::class,
            'role_id', // Foreign key on role_permissions table
            'id', // Foreign key on permissions table
            'role_id', // Local key on users table
            'permission_id' // Local key on role_permissions table
        );
    }
}
