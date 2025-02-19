<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property int $id
 * @property int $brawler_id
 * @property int $accessory_id
 */
class BrawlerAccessory extends Pivot
{
    protected $table = 'brawler_accessory';
}
