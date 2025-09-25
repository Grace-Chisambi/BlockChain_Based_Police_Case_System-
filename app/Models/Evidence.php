<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evidence extends Model
{
    use HasFactory;

    protected $table = 'evidence';
    protected $primaryKey = 'evidence_id';

    protected $fillable = [
        'case_id',
        'description',
        'file_path',
        'uploaded_by_staff_id',
        'review_status',
        'review_comment',
        'reviewed_at',
        'staff_id',
    ];

    /**
     * The police case this evidence belongs to.
     */
    public function case()
    {
        return $this->belongsTo(PoliceCase::class, 'case_id', 'case_id');
    }

    /**
     * The supervisor who reviewed the evidence.
     */
// App\Models\Evidence.php

public function reviewer()
{
    return $this->belongsTo(Staff::class, 'staff_id', 'staff_id');
}

public function uploader()
{
    return $this->belongsTo(Staff::class, 'uploaded_by_staff_id', 'staff_id');
}


    // (Optional) Remove this if `reviewer()` is being used consistently
    // public function staff()
    // {
    //     return $this->belongsTo(Staff::class, 'staff_id', 'staff_id');
    // }
}
