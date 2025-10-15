<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250625225150 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE recipe_position DROP CONSTRAINT FK_30BC416B59D8A214
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE recipe_position ADD CONSTRAINT FK_30BC416B59D8A214 FOREIGN KEY (recipe_id) REFERENCES recipe (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE season DROP CONSTRAINT FK_F0E45BA9933FE08C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE season ALTER ingredient_id DROP NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE season ADD CONSTRAINT FK_F0E45BA9933FE08C FOREIGN KEY (ingredient_id) REFERENCES ingredient (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE timer DROP CONSTRAINT FK_6AD0DE1A59D8A214
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE timer ADD CONSTRAINT FK_6AD0DE1A59D8A214 FOREIGN KEY (recipe_id) REFERENCES recipe (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE season DROP CONSTRAINT fk_f0e45ba9933fe08c
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE season ALTER ingredient_id SET NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE season ADD CONSTRAINT fk_f0e45ba9933fe08c FOREIGN KEY (ingredient_id) REFERENCES ingredient (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE recipe_position DROP CONSTRAINT fk_30bc416b59d8a214
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE recipe_position ADD CONSTRAINT fk_30bc416b59d8a214 FOREIGN KEY (recipe_id) REFERENCES recipe (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE timer DROP CONSTRAINT fk_6ad0de1a59d8a214
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE timer ADD CONSTRAINT fk_6ad0de1a59d8a214 FOREIGN KEY (recipe_id) REFERENCES recipe (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }
}
