<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CleaningReportCriteria extends Model
{
    use HasFactory;

    protected $table = 'cleaning_report_criteria';

    protected $fillable = ['cleaning_report_id', 'cleaning_criteria_id', 'response'];

    public function report()
    {
        return $this->belongsTo(CleaningReport::class, 'cleaning_report_id');
    }

    public function criteria()
    {
        return $this->belongsTo(CleaningCriteria::class, 'cleaning_criteria_id')->withTrashed();
    }
}
