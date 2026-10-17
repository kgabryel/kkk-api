<?php

namespace App\Tests\Helper\TestCase;

use App\Config\PhotoType;
use App\Entity\Photo;
use App\Entity\User;
use App\Tests\Factory\PhotoFactory;
use App\Tests\Helper\EntityFactory;
use App\Tests\Helper\EntityTestDataDto;
use App\Utils\PhotoUtils;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordToken;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;
use Zenstruck\Foundry\Test\Factories;

abstract class BaseFunctionalTestCase extends WebTestCase
{
    use Factories;

    protected KernelBrowser $client;
    protected EntityManagerInterface $entityManager;
    protected string $token;
    protected User $user;
    private array $filesList;

    protected function setUp(): void
    {
        self::ensureKernelShutdown();
        $this->client = static::createClient();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $schemaTool = new SchemaTool($this->entityManager);
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropDatabase();
        $schemaTool->createSchema($metadata);
        $this->filesList = $this->listFilesRecursively();
        [$this->user, $this->token] = $this->createUserAndGetToken(EntityFactory::USER_EMAIL);
    }

    protected function tearDown(): void
    {
        $newFiles = array_diff($this->listFilesRecursively(), $this->filesList);
        foreach ($newFiles as $file) {
            new Filesystem()->remove($file);
        }
    }

    public static function createPhoto(
        string $userEmail,
        array $data = [],
        string $fileContentSuffix = '-content',
    ): Photo {
        $data['user'] = EntityFactory::createUser($userEmail);
        $photo = PhotoFactory::createOne($data)->_real();
        self::$kernel->getProjectDir();
        $filesystem = new Filesystem();
        $filesystem->dumpFile(
            self::getPhotoPath(PhotoType::ORIGINAL, $photo->getFileName()),
            sprintf('original%s', $fileContentSuffix),
        );
        $filesystem->dumpFile(
            self::getPhotoPath(PhotoType::MEDIUM, $photo->getFileName()),
            sprintf('medium%s', $fileContentSuffix),
        );
        $filesystem->dumpFile(
            self::getPhotoPath(PhotoType::SMALL, $photo->getFileName()),
            sprintf('small%s', $fileContentSuffix),
        );

        return $photo;
    }

    protected function assertJsonResponseEquals(array $expected): void
    {
        $this->assertEquals($expected, json_decode($this->client->getResponse()->getContent(), true));
    }

    protected function assertJsonResponseSame(array $expected, bool $ignoreObjectKeyOrder = false): void
    {
        $response = json_decode($this->client->getResponse()->getContent(), true);
        if ($ignoreObjectKeyOrder) {
            $expected = $this->normalizeObjectsInArray($expected);
            $response = $this->normalizeObjectsInArray($response);
        }
        $this->assertSame($expected, $response);
    }

    protected function createEntitiesForAccessTest(array $items, callable $factoryMethod): int
    {
        $entityToFind = null;
        foreach ($items as $entity) {
            $assignedUserEmail = $entity['email'];
            $toFind = $entity['toFind'] ?? false;
            unset($entity['email'], $entity['toFind']);
            $createdEntity = $factoryMethod($assignedUserEmail, $entity);
            if (!$toFind) {
                continue;
            }
            $entityToFind = $createdEntity;
        }

        return $entityToFind?->getId() ?? 999999;
    }

    protected static function createResetPasswordRequest(string $userEmail, ?int $lifetime = null): ResetPasswordToken
    {
        $user = EntityFactory::createUser($userEmail);
        $resetPasswordHelper = self::getContainer()->get(ResetPasswordHelperInterface::class);

        return $resetPasswordHelper->generateResetToken($user, $lifetime);
    }

    protected function createUserAndGetToken(string $email = 'email@example.com'): array
    {
        $user = EntityFactory::createUser($email);
        $token = $this->getAccessToken($user);

        return [$user, $token];
    }

    protected function getAccessToken(User $user): string
    {
        return self::getContainer()->get(JWTEncoderInterface::class)->encode([
            'exp' => time() + 3600,
            'username' => $user->getUserIdentifier(),
        ]);
    }

    protected static function getPhotoPath(PhotoType $type, string $photoName): string
    {
        return PhotoUtils::getPath(self::$kernel->getProjectDir(), $type, $photoName);
    }

    protected function getResponseContent(): array
    {
        return json_decode($this->client->getResponse()->getContent(), true);
    }

    protected function prepareExpectedIndexResponseData(
        array $entitiesData,
        callable $entityCreate,
        callable $responseItemMapper,
        ?callable $pushCondition = null,
    ): array {
        if ($pushCondition === null) {
            $pushCondition = static function (EntityTestDataDto $entityData, User $user): bool {
                return $entityData->getUserEmail() === $user->getEmail();
            };
        }
        $expectedResponseData = [];

        /** @var EntityTestDataDto $entityData */
        foreach ($entitiesData as $entityData) {
            $tmp = $entityCreate($entityData);
            if (!$pushCondition($entityData, $this->user)) {
                continue;
            }
            array_unshift($expectedResponseData, $responseItemMapper($tmp));
        }

        return $expectedResponseData;
    }

    protected function save(object $entity): void
    {
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }

    protected function sendAuthorizedJsonRequest(string $method, string $url, array $content, string $token): void
    {
        $this->client->request(
            $method,
            $url,
            content: json_encode($content),
            server: ['HTTP_Authorization' => sprintf('Bearer %s', $token)],
        );
    }

    protected function sendAuthorizedRequest(string $method, string $url, string $token): void
    {
        $this->client->request(
            $method,
            $url,
            server: ['HTTP_Authorization' => sprintf('Bearer %s', $token)],
        );
    }

    private function listFilesRecursively(): array
    {
        $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('var/files'));
        $files = [];
        foreach ($rii as $file) {
            if (!$file->isFile()) {
                continue;
            }
            $files[] = $file->getRealPath();
        }

        return $files;
    }

    private function normalizeObjectsInArray(array $input): array
    {
        return array_map(static function (array $item): array {
            ksort($item);

            return $item;
        }, $input);
    }
}
