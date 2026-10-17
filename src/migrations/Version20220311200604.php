<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220311200604 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE recipe ALTER public DROP DEFAULT');
        $this->addSql('ALTER TABLE recipe_position ADD recipe_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE recipe_position ALTER ingredient_id DROP NOT NULL');
        $this->addSql(
            'ALTER TABLE recipe_position ADD CONSTRAINT FK_30BC416B59D8A214 FOREIGN KEY (recipe_id) REFERENCES recipe (id) NOT DEFERRABLE INITIALLY IMMEDIATE'
        );
        $this->addSql('CREATE INDEX IDX_30BC416B59D8A214 ON recipe_position (recipe_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE recipe_position DROP CONSTRAINT FK_30BC416B59D8A214');
        $this->addSql('DROP INDEX IDX_30BC416B59D8A214');
        $this->addSql('ALTER TABLE recipe_position DROP recipe_id');
        $this->addSql('ALTER TABLE recipe_position ALTER ingredient_id SET NOT NULL');
        $this->addSql('ALTER TABLE recipe ALTER public SET DEFAULT \'false\'');
    }
}
