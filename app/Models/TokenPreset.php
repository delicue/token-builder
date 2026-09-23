<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\{Fillable};
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $type
 * @property int $power
 * @property int $toughness
 * @property string $description
 * @property array $colors
 */
#[Fillable(['name', 'type', 'power', 'toughness', 'description', 'colors'])]
class TokenPreset extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'colors' => 'array',
        ];
    }
}
