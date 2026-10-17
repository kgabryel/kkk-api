<?php

namespace App\Tests\integration\Repository;

use App\Entity\Tag;
use App\Repository\FindOneByNameWithLowercaseTrait;
use App\Repository\TagRepository;
use App\Tests\DataProvider\TagDataProvider;
use App\Tests\Helper\TestCase\BaseIntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[Medium]
#[CoversTrait(FindOneByNameWithLowercaseTrait::class)]
class FindOneByNameWithLowercaseTraitTest extends BaseIntegrationTestCase
{
    private TagRepository $tagRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tagRepository = self::getContainer()->get(TagRepository::class);
    }

    #[Test]
    #[TestDox('Zwraca encję ignorując wielkość liter dla "{0}" i "{1}"')]
    #[DataProviderExternal(TagDataProvider::class, 'caseInsensitiveNameValues')]
    public function itFindsEntityIgnoringCase(string $storedName, string $searchName): void
    {
        // Arrange
        $tag = new Tag();
        $tag->setUser($this->defaultUser);
        $tag->setName($storedName);
        $this->save($tag);

        // Act
        $found = $this->tagRepository->findOneByNameWithLowercase($this->defaultUser, 'name', $searchName);

        // Assert
        $this->assertNotNull($found);
        $this->assertSame($storedName, $found->getName());
    }
}
