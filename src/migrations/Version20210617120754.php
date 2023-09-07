<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210617120754 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE recipe_position_group_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql(
            'CREATE TABLE recipe_position_group (id INT NOT NULL, recipe_id INT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))'
        );
        $this->addSql('CREATE INDEX IDX_9397328B59D8A214 ON recipe_position_group (recipe_id)');
        $this->addSql(
            'ALTER TABLE recipe_position_group ADD CONSTRAINT FK_9397328B59D8A214 FOREIGN KEY (recipe_id) REFERENCES recipe (id) NOT DEFERRABLE INITIALLY IMMEDIATE'
        );
        $this->addSql('ALTER TABLE recipe_position ADD recipe_position_group_id INT NOT NULL');
        $this->addSql(
            'ALTER TABLE recipe_position ADD CONSTRAINT FK_30BC416B83A6354 FOREIGN KEY (recipe_position_group_id) REFERENCES recipe_position_group (id) NOT DEFERRABLE INITIALLY IMMEDIATE'
        );
        $this->addSql('CREATE INDEX IDX_30BC416B83A6354 ON recipe_position (recipe_position_group_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE recipe_position DROP CONSTRAINT FK_30BC416B83A6354');
        $this->addSql('DROP SEQUENCE recipe_position_group_id_seq CASCADE');
        $this->addSql('DROP TABLE recipe_position_group');
        $this->addSql('DROP INDEX IDX_30BC416B83A6354');
        $this->addSql('ALTER TABLE recipe_position DROP recipe_position_group_id');
    }
}
