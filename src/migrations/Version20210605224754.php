<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210605224754 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE recipe_position_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql(
            'CREATE TABLE recipe_position (id INT NOT NULL, recipe_id INT NOT NULL, ingredient_id INT NOT NULL, amount DOUBLE PRECISION NOT NULL, measure VARCHAR(255) NOT NULL, PRIMARY KEY(id))'
        );
        $this->addSql('CREATE INDEX IDX_30BC416B59D8A214 ON recipe_position (recipe_id)');
        $this->addSql('CREATE INDEX IDX_30BC416B933FE08C ON recipe_position (ingredient_id)');
        $this->addSql(
            'ALTER TABLE recipe_position ADD CONSTRAINT FK_30BC416B59D8A214 FOREIGN KEY (recipe_id) REFERENCES recipe (id) NOT DEFERRABLE INITIALLY IMMEDIATE'
        );
        $this->addSql(
            'ALTER TABLE recipe_position ADD CONSTRAINT FK_30BC416B933FE08C FOREIGN KEY (ingredient_id) REFERENCES ingredient (id) NOT DEFERRABLE INITIALLY IMMEDIATE'
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE recipe_position_id_seq CASCADE');
        $this->addSql('DROP TABLE recipe_position');
    }
}
