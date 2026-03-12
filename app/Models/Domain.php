<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Domain extends Model
{
    use HasFactory;

    protected $fillable = ['domain', 'zone_id'];

    public function services()
    {
        return $this->hasMany(Service::class);
    }
}
