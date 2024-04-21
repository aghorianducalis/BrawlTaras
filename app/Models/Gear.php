<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $ext_id
 * @property string $name
 * @property int $level
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Gear extends Model
{
    use HasFactory;

    protected $table = 'gears';

    protected $fillable = [
        'ext_id',
        'name',
        'level',
    ];

    protected $casts = [
        'ext_id' => 'integer',
        'level'  => 'integer',
    ];
}
