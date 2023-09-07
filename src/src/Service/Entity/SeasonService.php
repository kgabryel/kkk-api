<?php

namespace App\Service\Entity;

use App\Entity\Season;
use App\Model\UpdateSeason;
use App\Repository\SeasonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class SeasonService extends EntityService
{
    private Season $season;
    private SeasonRepository $seasonRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage,
        SeasonRepository $seasonRepository
    ) {
        parent::__construct($entityManager, $tokenStorage);
        $this->seasonRepository = $seasonRepository;
    }

    public function find(int $id): bool
    {
        $season = $this->seasonRepository->findById($id, $this->user);
        if ($season === null) {
            return false;
        }
        $this->season = $season;

        return true;
    }

    public function getSeason(): Season
    {
        return $this->season;
    }

    public function update(FormInterface $form, Request $request): bool
    {
        $form->handleRequest($request);
        if (!$form->isSubmitted() || !$form->isValid()) {
            return false;
        }
        /** @var UpdateSeason $data */
        $data = $form->getData();
        $this->season->setStart($data->getStart());
        $this->season->setStop($data->getStop());
        $this->saveEntity($this->season);

        return true;
    }

    public function remove(): void
    {
        $this->removeEntity($this->season);
    }
}
