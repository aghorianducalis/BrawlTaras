<?php

declare(strict_types=1);

namespace App\Services\Application\Contracts\Battle;

use App\API\DTO\Response\Battle\BattleLogDTO;
use App\Models\Battle\Battle;
use Illuminate\Validation\ValidationException;

interface BattleLogInteractorInterface
{
    /**
     * Create new battles while ignoring existing ones.
     *
     * @param BattleLogDTO $dto
     * @return Battle[] array of battles
     * @throws ValidationException
     */
    public function syncBattleLog(BattleLogDTO $dto): array;
}
