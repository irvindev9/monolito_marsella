<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Restriction extends Model
{
    use HasFactory;

    protected $fillable = ['house_id', 'block_date_end', 'block_by', 'reason', 'is_active'];

    public function blockBy()
    {
        return $this->belongsTo(User::class, 'block_by');
    }

    public function house()
    {
        return $this->belongsTo(House::class);
    }
}
