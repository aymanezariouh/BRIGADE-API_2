<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    use HasFactory;

    public const TAGS = [
        'contains_meat',
        'contains_sugar',
        'contains_cholesterol',
        'contains_gluten',
        'contains_lactose',
    ];

    protected $fillable = [
        'name',
        'tags',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
        ];
    }

    public function plats()
    {
        return $this->belongsToMany(Plat::class, 'plate_ingredient', 'ingredient_id', 'plate_id')
            ->withTimestamps();
    }

    public function plates()
    {
        return $this->belongsToMany(Plate::class, 'plate_ingredient', 'ingredient_id', 'plate_id')
            ->withTimestamps();
    }
}
