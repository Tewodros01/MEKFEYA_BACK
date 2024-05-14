<?php

namespace app\Models\Setting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class LocationModel extends Model
{
    use HasFactory;

    protected $table = 'locations';

    protected $fillable = [
        'location_name',
        "location_code"
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
