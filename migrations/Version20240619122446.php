<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240619122446 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE trace_competence (id INT AUTO_INCREMENT NOT NULL, trace_id INT DEFAULT NULL, apc_niveau_id INT DEFAULT NULL, apc_apprentissage_critique_id INT DEFAULT NULL, INDEX IDX_C73435CABE0D4B70 (trace_id), INDEX IDX_C73435CA9445617E (apc_niveau_id), INDEX IDX_C73435CA4DD72DCD (apc_apprentissage_critique_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('ALTER TABLE trace_competence ADD CONSTRAINT FK_C73435CABE0D4B70 FOREIGN KEY (trace_id) REFERENCES trace (id)');
        $this->addSql('ALTER TABLE trace_competence ADD CONSTRAINT FK_C73435CA9445617E FOREIGN KEY (apc_niveau_id) REFERENCES apc_niveau (id)');
        $this->addSql('ALTER TABLE trace_competence ADD CONSTRAINT FK_C73435CA4DD72DCD FOREIGN KEY (apc_apprentissage_critique_id) REFERENCES apc_apprentissage_critique (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE trace_competence DROP FOREIGN KEY FK_C73435CABE0D4B70');
        $this->addSql('ALTER TABLE trace_competence DROP FOREIGN KEY FK_C73435CA9445617E');
        $this->addSql('ALTER TABLE trace_competence DROP FOREIGN KEY FK_C73435CA4DD72DCD');
        $this->addSql('DROP TABLE trace_competence');
    }
}
