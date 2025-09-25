<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlockchainLog extends Model
{
    protected $fillable = [
        'case_id',
        'tx_hash',
        'action_type',
        'payload',
    ];
}
