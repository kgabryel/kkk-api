<?php

namespace App\Service\Entity;

use App\Entity\Season;
use App\Repository\SeasonRepository;
use App\Service\UserService;
use App\Validation\EditSeasonValidation;
use Doctrine\ORM\EntityManagerInterface;

class SeasonService extends EntityService
{
    private SeasonRepository $seasonRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserService $userService,
        SeasonRepository $seasonRepository,
    ) {
        parent::__construct($entityManager, $userService);
        $this->seasonRepository = $seasonRepository;
    }

    public function find(int $id): ?Season
    {
        return $this->seasonRepository->findById($id, $this->user);
    }

    public function remove(Season $season): void
    {
        $this->removeEntity($season);
    }

    public function update(Season $season, EditSeasonValidation $editSeasonValidation): bool
    {
        if (!$editSeasonValidation->validate()->passed()) {
            return false;
        }
        $data = $editSeasonValidation->getDto();
        $season->setStart($data->getStart());
        $season->setStop($data->getStop());
        $this->saveEntity($season);

        return true;
    }
}
