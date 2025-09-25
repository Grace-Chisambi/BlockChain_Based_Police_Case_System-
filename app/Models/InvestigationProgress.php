<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvestigationProgress extends Model
{
    use HasFactory;

    protected $table = 'investigation_progress';

    protected $primaryKey = 'progress_id';

    protected $fillable = [
        'case_id',
        'staff_id',
        'date',
        'notes',
    ];

    // Enable automatic timestamps
    public $timestamps = true;


    public function case()
    {
        return $this->belongsTo(PoliceCase::class, 'case_id', 'case_id');
    }

    public function investigator()
    {
        return $this->belongsTo(Staff::class, 'staff_id', 'staff_id');
    }
}
