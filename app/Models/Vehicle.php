<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['user_id', 'plate_number'];

    protected static function booted(): void
    {
        static::creating(function (Vehicle $vehicle) {
            if (auth()->check()) {
                $vehicle->user_id = auth()->id();
            }
        });

        static::addGlobalScope('user', function (Builder $builder) {
            $builder->when(auth()->id(), function (Builder $builder) {
                return $builder->where('user_id', auth()->id());
            });
        });
    }
}
