<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CaseAssignment extends Model
{
    use HasFactory;

    protected $table = 'case_assignments';
    protected $primaryKey = 'assignment_id';

    protected $fillable = [
        'case_id',
        'staff_id',
        'role',
    ];

    // Relationship to the PoliceCase model
    public function policeCase()
    {
        return $this->belongsTo(PoliceCase::class, 'case_id', 'case_id');
    }
   
    // Relationship to the User model
    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id', 'staff_id');
    }
    // Supervisor (assigned the case)
public function supervisor()
{
    return $this->belongsTo(Staff::class, 'assigned_by', 'staff_id');
}

public function user()
    {
        return $this->hasOneThrough(
            User::class,
            Staff::class,
            'staff_id',   // Foreign key on PoliceStaff table...
            'user_id',    // Foreign key on Users table...
            'staff_id',   // Local key on CaseAssignment table...
            'user_id'     // Local key on PoliceStaff table...
        );
    }


}
