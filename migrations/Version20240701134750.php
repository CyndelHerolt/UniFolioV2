<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240701134750 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE criteres_apc_apprentissage_critique DROP FOREIGN KEY FK_782D12334DD72DCD');
        $this->addSql('ALTER TABLE criteres_apc_apprentissage_critique DROP FOREIGN KEY FK_782D1233A6EB9800');
        $this->addSql('ALTER TABLE criteres_apc_niveau DROP FOREIGN KEY FK_A9FE59029445617E');
        $this->addSql('ALTER TABLE criteres_apc_niveau DROP FOREIGN KEY FK_A9FE5902A6EB9800');
        $this->addSql('DROP TABLE criteres_apc_apprentissage_critique');
        $this->addSql('DROP TABLE criteres_apc_niveau');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE criteres_apc_apprentissage_critique (criteres_id INT NOT NULL, apc_apprentissage_critique_id INT NOT NULL, INDEX IDX_782D12334DD72DCD (apc_apprentissage_critique_id), INDEX IDX_782D1233A6EB9800 (criteres_id), PRIMARY KEY(criteres_id, apc_apprentissage_critique_id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE criteres_apc_niveau (criteres_id INT NOT NULL, apc_niveau_id INT NOT NULL, INDEX IDX_A9FE5902A6EB9800 (criteres_id), INDEX IDX_A9FE59029445617E (apc_niveau_id), PRIMARY KEY(criteres_id, apc_niveau_id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE criteres_apc_apprentissage_critique ADD CONSTRAINT FK_782D12334DD72DCD FOREIGN KEY (apc_apprentissage_critique_id) REFERENCES apc_apprentissage_critique (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE criteres_apc_apprentissage_critique ADD CONSTRAINT FK_782D1233A6EB9800 FOREIGN KEY (criteres_id) REFERENCES criteres (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE criteres_apc_niveau ADD CONSTRAINT FK_A9FE59029445617E FOREIGN KEY (apc_niveau_id) REFERENCES apc_niveau (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE criteres_apc_niveau ADD CONSTRAINT FK_A9FE5902A6EB9800 FOREIGN KEY (criteres_id) REFERENCES criteres (id) ON UPDATE NO ACTION ON DELETE CASCADE');
    }
}
