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
 * @property string $color
 */
#[Fillable(['name', 'type', 'power', 'toughness', 'description', 'color'])]
class TokenPreset extends Model
{
}
