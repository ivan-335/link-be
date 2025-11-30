<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Matrix;

class Cell extends Model
{
    protected $fillable = ['matrix_id', 'row', 'col', 'height'];

    public function matrix()
    {
        return $this->belongsTo(Matrix::class);
    }
}
