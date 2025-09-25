<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    protected $table = 'police_staff';
    protected $primaryKey = 'staff_id';

    protected $fillable = [
        'user_id',
        'department_id',
        'available',
        'specialization',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function assignedCases()
    {
        return $this->belongsToMany(PoliceCase::class, 'case_assignments', 'staff_id', 'case_id')
                    ->withPivot('role');
    }
}
