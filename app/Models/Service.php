<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'domain_id',
        'subdomain',
        'full_domain',
        'port',
        'status'
    ];

    public function domain()
    {
        return $this->belongsTo(Domain::class);
    }
}
