<?php

namespace App\Tests\integration\Repository;

use App\Entity\RefreshToken;
use App\Repository\RefreshTokenRepository;
use App\Tests\Helper\TestCase\BaseIntegrationTestCase;
use DateTime;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[Medium]
#[CoversClass(RefreshTokenRepository::class)]
class RefreshTokenRepositoryTest extends BaseIntegrationTestCase
{
    private RefreshTokenRepository $refreshTokenRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->refreshTokenRepository = self::getContainer()->get(RefreshTokenRepository::class);
    }

    #[Test]
    #[TestDox('Zwraca tokeny wygasłe względem podanej daty')]
    public function itFindsExpiredTokensBeforeCutoff(): void
    {
        // Arrange
        $cutoff = new DateTime('2025-01-01 00:00:00');
        $token1 = new RefreshToken();
        $token1->setRefreshToken('token_before');
        $token1->setValid(new DateTime('2024-12-31 23:59:59'));
        $token1->setUsername('user');
        $token2 = new RefreshToken();
        $token2->setRefreshToken('token_after');
        $token2->setValid(new DateTime('2025-01-01 00:00:01'));
        $token2->setUsername('user');
        $this->save($token1);
        $this->save($token2);

        // Act
        $result = $this->refreshTokenRepository->findInvalid($cutoff);

        // Assert
        $this->assertCount(1, $result);
        $this->assertSame('token_before', $result[0]->getRefreshToken());
    }

    #[Test]
    #[TestDox('Nie zwraca tokenów, gdy wszystkie są ważne')]
    public function itReturnsEmptyWhenNoExpiredTokens(): void
    {
        // Arrange
        $token = new RefreshToken();
        $token->setRefreshToken('still_valid');
        $token->setValid((new DateTime('+2 hours')));
        $token->setUsername('user');
        $this->save($token);

        // Act
        $result = $this->refreshTokenRepository->findInvalid();

        // Assert
        $this->assertEmpty($result);
    }

    #[Test]
    #[TestDox('Zwraca tokeny wygasłe względem bieżącego czasu')]
    public function itReturnsExpiredTokensRelativeToNow(): void
    {
        // Arrange
        $token1 = new RefreshToken();
        $token1->setRefreshToken('expired_token');
        $token1->setValid((new DateTime('-1 hour')));
        $token1->setUsername('user');
        $token2 = new RefreshToken();
        $token2->setRefreshToken('valid_token');
        $token2->setValid((new DateTime('+1 hour')));
        $token2->setUsername('user');
        $this->save($token1);
        $this->save($token2);

        // Act
        $result = $this->refreshTokenRepository->findInvalid();

        // Assert
        $this->assertCount(1, $result);
        $this->assertSame('expired_token', $result[0]->getRefreshToken());
    }
}
