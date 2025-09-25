<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    use HasFactory;

    protected $table = 'complaints';

    protected $primaryKey = 'complaint_id';

    public $timestamps = true;

    protected $fillable = [ 'fname', 'sname', 'age', 'village', 'job', 'phone_number', 'statement' ,  'latitude', 'longitude'];

    public function cases()
{
    return $this->hasMany(PoliceCase::class, 'complaint_id', 'complaint_id');
}

}
