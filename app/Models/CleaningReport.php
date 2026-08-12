<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CleaningReport extends Model
{
    use HasFactory;

    protected $fillable = ['reservation_id', 'reviewer_user_id', 'completed_at'];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_user_id');
    }

    public function criteria()
    {
        return $this->hasMany(CleaningReportCriteria::class);
    }
}
