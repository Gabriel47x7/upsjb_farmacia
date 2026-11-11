<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categorias extends Model
{
    protected $table = 'categorias'; // Tabla en tu base de datos
    protected $fillable = ['nombre', 'descripcion'];

    public function medicines(): HasMany
    {
        return $this->hasMany(Medicine::class);
    }
}