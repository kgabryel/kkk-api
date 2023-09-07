<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210617121058 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE recipe_position DROP CONSTRAINT fk_30bc416bd0d47e04');
        $this->addSql('DROP SEQUENCE group_id_seq CASCADE');
        $this->addSql('DROP TABLE "group"');
        $this->addSql('ALTER TABLE recipe_position DROP CONSTRAINT fk_30bc416b59d8a214');
        $this->addSql('DROP INDEX idx_30bc416b59d8a214');
        $this->addSql('DROP INDEX idx_30bc416bd0d47e04');
        $this->addSql('ALTER TABLE recipe_position DROP recipe_id');
        $this->addSql('ALTER TABLE recipe_position DROP positions_group_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SEQUENCE group_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE "group" (id INT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE recipe_position ADD recipe_id INT NOT NULL');
        $this->addSql('ALTER TABLE recipe_position ADD positions_group_id INT DEFAULT NULL');
        $this->addSql(
            'ALTER TABLE recipe_position ADD CONSTRAINT fk_30bc416b59d8a214 FOREIGN KEY (recipe_id) REFERENCES recipe (id) NOT DEFERRABLE INITIALLY IMMEDIATE'
        );
        $this->addSql(
            'ALTER TABLE recipe_position ADD CONSTRAINT fk_30bc416bd0d47e04 FOREIGN KEY (positions_group_id) REFERENCES "group" (id) NOT DEFERRABLE INITIALLY IMMEDIATE'
        );
        $this->addSql('CREATE INDEX idx_30bc416b59d8a214 ON recipe_position (recipe_id)');
        $this->addSql('CREATE INDEX idx_30bc416bd0d47e04 ON recipe_position (positions_group_id)');
    }
}
