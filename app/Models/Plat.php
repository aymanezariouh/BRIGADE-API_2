<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plat extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'user_id',
        'category_id',
        'image',
        'is_available',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_available' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function categories()
    {
        return $this->category();
    }

    public function ingredients()
    {
        return $this->belongsToMany(Ingredient::class, 'plate_ingredient', 'plate_id', 'ingredient_id')
            ->withTimestamps();
    }

    public function recommendations()
    {
        return $this->hasMany(Recommendation::class, 'plate_id');
    }
}
