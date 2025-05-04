<?php

declare(strict_types=1);

namespace App\Services\Application;

use App\API\DTO\Response\Battle\BattleLogDTO;
use App\Services\Application\Contracts\Battle\BattleLogInteractorInterface;
use App\Services\Repositories\Contracts\Battle\BattleRepositoryInterface;

final readonly class BattleLogInteractor implements BattleLogInteractorInterface
{
    public function __construct(
        private BattleRepositoryInterface $battleRepository,
    ) {}

    public function syncBattleLog(BattleLogDTO $dto): array
    {
        $battles = [];

        foreach ($dto->battles as $battleDTO) {
            $battle = $this->battleRepository->findOrCreateBattleFromDTO(battleDTO: $battleDTO);
            $battle->load([
                'battleResults',
            ]);

            $battles[$battle->id] = $battle;
        }

        return $battles;
    }
}
