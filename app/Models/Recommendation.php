<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recommendation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'plate_id',
        'score',
        'label',
        'warning_message',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plate()
    {
        return $this->belongsTo(Plate::class, 'plate_id');
    }

    public function plat()
    {
        return $this->plate();
    }
}
