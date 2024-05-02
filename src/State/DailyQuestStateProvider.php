<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\DailyQuest;
use App\Enum\DailyQuestStatusEnum;
use Exception;

class DailyQuestStateProvider implements ProviderInterface
{
    /**
     * @throws Exception
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        return $this->createQuests();
    }

    /**
     * @throws Exception
     */
    private function createQuests(): array
    {
        $quests = [];
        for ($i = 0; $i < 50; $i++) {
            $quest = new DailyQuest(new \DateTimeImmutable(sprintf('- %d days', $i)));
            $quest->questName = sprintf('Quest %d', $i);
            $quest->description = sprintf('Description %d', $i);
            $quest->difficultyLevel = $i % 10;
            $quest->status = $i % 2 === 0 ? DailyQuestStatusEnum::ACTIVE : DailyQuestStatusEnum::COMPLETED;
            $quests[$quest->getDayString()] = $quest;
        }
        return $quests;
    }
}
