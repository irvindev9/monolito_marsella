<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Directory extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'position', 'phone', 'description', 'photo', 'order'];

    public function photoUrl()
    {
        return $this->photo ? Storage::url($this->photo) : null;
    }


}
