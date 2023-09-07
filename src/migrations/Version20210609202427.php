<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210609202427 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE recipe_position ADD positions_group_id INT DEFAULT NULL');
        $this->addSql(
            'ALTER TABLE recipe_position ADD CONSTRAINT FK_30BC416BD0D47E04 FOREIGN KEY (positions_group_id) REFERENCES "group" (id) NOT DEFERRABLE INITIALLY IMMEDIATE'
        );
        $this->addSql('CREATE INDEX IDX_30BC416BD0D47E04 ON recipe_position (positions_group_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE recipe_position DROP CONSTRAINT FK_30BC416BD0D47E04');
        $this->addSql('DROP INDEX IDX_30BC416BD0D47E04');
        $this->addSql('ALTER TABLE recipe_position DROP positions_group_id');
    }
}
