<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240217135350 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE validation (id INT AUTO_INCREMENT NOT NULL, date_creation DATE NOT NULL, date_modification DATE DEFAULT NULL, etat INT NOT NULL, trace_id INT NOT NULL, apc_niveau_id INT NOT NULL, enseignant_id INT DEFAULT NULL, INDEX IDX_16AC5B6EBE0D4B70 (trace_id), INDEX IDX_16AC5B6E9445617E (apc_niveau_id), INDEX IDX_16AC5B6EE455FCC0 (enseignant_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('ALTER TABLE validation ADD CONSTRAINT FK_16AC5B6EBE0D4B70 FOREIGN KEY (trace_id) REFERENCES trace (id)');
        $this->addSql('ALTER TABLE validation ADD CONSTRAINT FK_16AC5B6E9445617E FOREIGN KEY (apc_niveau_id) REFERENCES apc_niveau (id)');
        $this->addSql('ALTER TABLE validation ADD CONSTRAINT FK_16AC5B6EE455FCC0 FOREIGN KEY (enseignant_id) REFERENCES enseignant (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE validation DROP FOREIGN KEY FK_16AC5B6EBE0D4B70');
        $this->addSql('ALTER TABLE validation DROP FOREIGN KEY FK_16AC5B6E9445617E');
        $this->addSql('ALTER TABLE validation DROP FOREIGN KEY FK_16AC5B6EE455FCC0');
        $this->addSql('DROP TABLE validation');
    }
}
