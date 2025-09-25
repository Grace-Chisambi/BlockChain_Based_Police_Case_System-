<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaseClosure extends Model
{
    use HasFactory;

    protected $table = 'case_closures';

    protected $primaryKey = 'closure_id';

    protected $fillable = [
        'case_id',
        'staff_id',
        'closure_type',
        'reason',
        'closure_date',
    ];

    // Optional: relationships

    public function case()
    {
        return $this->belongsTo(CaseModel::class, 'case_id');
    }

    public function staff()
    {
        return $this->belongsTo(PoliceStaff::class, 'staff_id');
    }
}
