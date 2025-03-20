<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property int $id
 * @property int $brawler_id
 * @property int $star_power_id
 */
class BrawlerStarPower extends Pivot
{
    protected $table = 'brawler_star_power';
}
