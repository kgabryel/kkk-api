<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250625234333 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE photo DROP CONSTRAINT FK_14B7841859D8A214
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE photo ADD CONSTRAINT FK_14B7841859D8A214 FOREIGN KEY (recipe_id) REFERENCES recipe (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE recipe_position DROP CONSTRAINT FK_30BC416B83A6354
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE recipe_position ADD CONSTRAINT FK_30BC416B83A6354 FOREIGN KEY (recipe_position_group_id) REFERENCES recipe_position_group (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE recipe_position_group DROP CONSTRAINT FK_9397328B59D8A214
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE recipe_position_group ADD CONSTRAINT FK_9397328B59D8A214 FOREIGN KEY (recipe_id) REFERENCES recipe (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE recipe_position DROP CONSTRAINT fk_30bc416b83a6354
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE recipe_position ADD CONSTRAINT fk_30bc416b83a6354 FOREIGN KEY (recipe_position_group_id) REFERENCES recipe_position_group (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE recipe_position_group DROP CONSTRAINT fk_9397328b59d8a214
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE recipe_position_group ADD CONSTRAINT fk_9397328b59d8a214 FOREIGN KEY (recipe_id) REFERENCES recipe (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE photo DROP CONSTRAINT fk_14b7841859d8a214
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE photo ADD CONSTRAINT fk_14b7841859d8a214 FOREIGN KEY (recipe_id) REFERENCES recipe (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }
}
