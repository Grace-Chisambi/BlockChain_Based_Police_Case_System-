<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PoliceCase extends Model
{
    use HasFactory;

    protected $table = 'cases';
    protected $primaryKey = 'case_id';

    protected $fillable = [
        'complaint_id',
        'department_id',
        'case_number',
        'case_type',
        'case_status',
        'priority',
        'created_at',
        'updated_at',
        'suspect_reviewed',
    ];

    // Relationships
    public function complaint()
    {
        return $this->belongsTo(Complaint::class, 'complaint_id');
    }

    public function assignments()
    {
        return $this->hasMany(CaseAssignment::class, 'case_id', 'case_id');
    }

    public function closure()
    {
        return $this->hasOne(CaseClosure::class, 'case_id');
    }

    public function evidence()
    {
        return $this->hasMany(Evidence::class, 'case_id', 'case_id');
    }
public function department()
{
    return $this->belongsTo(Department::class, 'department_id', 'department_id');
}
public function user()
{
    return $this->belongsTo(User::class, 'user_id');
}
    public function staff()
    {
        return $this->belongsTo(PoliceStaff::class, 'staff_id');
    }
    public function progress()
{
    return $this->hasMany(InvestigationProgress::class, 'case_id', 'case_id');
}

public function prosecutor()
{
    return $this->belongsTo(Staff::class, 'staff_id');
}







    // Cast dates properly for Carbon instances
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
