<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Cell;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Matrix extends Model
{
    use HasFactory;
    
    protected $fillable = ['name', 'size'];

    public function cells()
    {
        return $this->hasMany(Cell::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
