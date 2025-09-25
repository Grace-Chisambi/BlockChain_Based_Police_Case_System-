<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Suspect extends Model
{
    use HasFactory;

    protected $table = 'suspects';
    protected $primaryKey = 'suspect_id';


    protected $fillable = [
        'case_id',
        'fname',
        'sname',
        'age',
        'village',
        'job',
        'phone_number',
        'statement',
        'status',
        'recommendation',
        'decision',
        'reviewed_by_staff_id',
        'review_date',
    ];

    public function case()
    {
        return $this->belongsTo(PoliceCase::class, 'case_id', 'case_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(PoliceStaff::class, 'reviewed_by_staff_id', 'staff_id');
    }
}
