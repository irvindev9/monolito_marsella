<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class House extends Model
{
    use HasFactory;

    protected $fillable = ['street_id', 'house_number'];

    public function street()
    {
        return $this->belongsTo(Street::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function primaryUser()
    {
        return $this->users()->where('is_primary', true)->first()
            ?? $this->users()->first();
    }
}
