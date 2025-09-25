<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $table = 'departments';

    protected $fillable = [
        'name',
        'description',
        'created_at',
        'updated_at',
    ];

    public function staff()
    {
        return $this->hasMany(Staff::class);
    }
}
