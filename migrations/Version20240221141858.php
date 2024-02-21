<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240221141858 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE validation ADD apc_apprentissage_critique_id INT DEFAULT NULL, CHANGE apc_niveau_id apc_niveau_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE validation ADD CONSTRAINT FK_16AC5B6E4DD72DCD FOREIGN KEY (apc_apprentissage_critique_id) REFERENCES apc_apprentissage_critique (id)');
        $this->addSql('CREATE INDEX IDX_16AC5B6E4DD72DCD ON validation (apc_apprentissage_critique_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE validation DROP FOREIGN KEY FK_16AC5B6E4DD72DCD');
        $this->addSql('DROP INDEX IDX_16AC5B6E4DD72DCD ON validation');
        $this->addSql('ALTER TABLE validation DROP apc_apprentissage_critique_id, CHANGE apc_niveau_id apc_niveau_id INT NOT NULL');
    }
}
