<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property Carbon $played_on
 * @property array<int, array<string, mixed>> $cards
 * @property array<int, int>|null $counters
 */
#[Fillable(['user_id', 'name', 'played_on', 'cards', 'counters'])]
class TokenSession extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'played_on' => 'date',
            'cards' => 'array',
            'counters' => 'array',
        ];
    }

    /**
     * The user who saved this session.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
