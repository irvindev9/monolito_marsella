<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentSettingLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'user_name',
        'changes',
        'created_at',
    ];

    protected $casts = [
        'changes' => 'array',
        'created_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        // Audit log entries are immutable and cannot be deleted.
        static::deleting(fn () => false);
    }
}
