<?php

namespace App\Validation;

use App\Config\LengthConfig;
use App\Dto\Request\Tag;
use App\Repository\TagRepository;
use App\Service\UserService;
use App\ValidationPolicy\RequiredString;
use App\ValidatorRule\UniqueNameForUser\UniqueNameForUser;
use RuntimeException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Sequentially;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TagValidation extends BaseValidation
{
    private int $expect;
    private TagRepository $tagRepository;

    public function __construct(
        ValidatorInterface $validator,
        RequestStack $requestStack,
        UserService $userService,
        TagRepository $tagRepository,
    ) {
        parent::__construct($validator, $requestStack, $userService);
        $this->tagRepository = $tagRepository;
        $this->expect = 0;
    }

    public function getDto(): Tag
    {
        if (!$this->validate) {
            throw new RuntimeException('Cannot access DTO before successful validation.');
        }

        return new Tag($this->data['name']);
    }

    public function setExpect(int $expect): void
    {
        $this->expect = $expect;
    }

    protected function getRules(): Collection
    {
        return new Collection(
            [
                'name' => new Sequentially([
                    new RequiredString(LengthConfig::TAG),
                    new UniqueNameForUser($this->tagRepository, $this->user, 'name', $this->expect),
                ]),
            ],
            allowExtraFields: false,
        );
    }

    protected function normalizeData(): void
    {
        if (!isset($this->data['name']) || !is_string($this->data['name'])) {
            return;
        }

        $this->data['name'] = trim($this->data['name']);
    }
}
