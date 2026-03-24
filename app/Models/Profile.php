<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;

    public const DIETARY_TAGS = [
        'vegan',
        'no_sugar',
        'no_cholesterol',
        'gluten_free',
        'no_lactose',
    ];

    protected $fillable = [
        'user_id',
        'phone',
        'address',
        'city',
        'dietary_tags',
        'allergies',
    ];

    protected function casts(): array
    {
        return [
            'dietary_tags' => 'array',
            'allergies' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
