<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CleaningCriteria extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'cleaning_criteria';

    protected $fillable = ['name', 'response_type', 'is_active', 'order'];
}
