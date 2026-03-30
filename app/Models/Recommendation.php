<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recommendation extends Model
{
    use HasFactory;

    public const STATUS_PROCESSING = 'processing';
    public const STATUS_READY = 'ready';

    public const LABEL_HIGHLY_RECOMMENDED = 'Highly Recommended';
    public const LABEL_RECOMMENDED_WITH_NOTES = 'Recommended with notes';
    public const LABEL_NOT_RECOMMENDED = 'Not Recommended';

    protected $fillable = [
        'user_id',
        'plate_id',
        'score',
        'label',
        'warning_message',
        'details',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
            'details' => 'array',
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
