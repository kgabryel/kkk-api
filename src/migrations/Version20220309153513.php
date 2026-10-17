<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\Recipe;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220309153513 extends AbstractMigration
{

    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $sql = "select id from recipe";
        $stmt = $this->connection->prepare($sql);
        $recipes = $stmt->executeQuery()->fetchAllAssociative();
        foreach ($recipes as $recipe) {
            $this->connection->update(
                'recipe',
                ['public_id' => Uuid::uuid4()->toString()],
                ['id' => $recipe['id']]
            );
        }
        $this->addSql('ALTER TABLE recipe alter column public_id set NOT NULL');
    }

    public function down(Schema $schema): void
    {
    }
}
