<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = ['house_id', 'user_id', 'reservation_date', 'is_approved', 'is_visible', 'notes'];
}
