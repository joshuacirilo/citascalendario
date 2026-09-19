<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Doctor extends Model
{
    protected $table = 'doctores';

    protected $fillable = [
        'nombres',
        'apellidos',
        'especialidad',
        'telefono',
        'email',
    ];

    public function citas(): HasMany
    {
        return $this->hasMany(Cita::class);
    }
}
