<?php

namespace App\State;

use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\Pagination\TraversablePaginator;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\DailyQuest;
use App\ApiResource\QuestTreasure;
use App\Enum\DailyQuestStatusEnum;
use App\Repository\DragonTreasureRepository;
use Exception;

class DailyQuestStateProvider implements ProviderInterface
{
    public function __construct(
        private DragonTreasureRepository $treasureRepository,
        private Pagination $pagination
    )
    {
    }

    /**
     * @throws Exception
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if($operation instanceof CollectionOperationInterface) {
            $currentPage = $this->pagination->getPage($context);
            $itemsPerPage = $this->pagination->getLimit($operation, $context);
            $offset = $this->pagination->getOffset($operation, $context);
            $totalItems = $this->countTotalQuests();

            $quest = $this->createQuests($offset, $itemsPerPage);

            return new TraversablePaginator(
                new \ArrayIterator($quest),
                $currentPage,
                $itemsPerPage,
                $totalItems
            );
        }

        $quests = $this->createQuests(0, 50);

        return $quests[$uriVariables['dayString']] ?? null;
    }

    /**
     * @throws Exception
     */
    private function createQuests(int $offset, int $limit): array
    {
        $treasures = $this->treasureRepository->findBy([], [], 10 );

        $quests = [];
        for ($i = $offset; $i < ($offset + $limit); $i++) {
            $quest = new DailyQuest(new \DateTimeImmutable(sprintf('- %d days', $i)));
            $quest->questName = sprintf('Quest %d', $i);
            $quest->description = sprintf('Description %d', $i);
            $quest->difficultyLevel = $i % 10;
            $quest->status = $i % 2 === 0 ? DailyQuestStatusEnum::ACTIVE : DailyQuestStatusEnum::COMPLETED;
            $quest->lastUpdated = new \DateTimeImmutable(sprintf('- %d days', random_int(10, 100)));
            $randomTreasures = $treasures[array_rand($treasures)];
            $quest->treasure = new QuestTreasure(
                $randomTreasures->getName(),
                $randomTreasures->getValue(),
                $randomTreasures->getCoolFactor()
            );

            $quests[$quest->getDayString()] = $quest;
        }
        return $quests;
    }

    private function countTotalQuests(): int
    {
        return 50;
    }
}
