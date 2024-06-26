<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240626115436 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE trace_competence ADD portfolio_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE trace_competence ADD CONSTRAINT FK_C73435CAB96B5643 FOREIGN KEY (portfolio_id) REFERENCES portfolio_univ (id)');
        $this->addSql('CREATE INDEX IDX_C73435CAB96B5643 ON trace_competence (portfolio_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE trace_competence DROP FOREIGN KEY FK_C73435CAB96B5643');
        $this->addSql('DROP INDEX IDX_C73435CAB96B5643 ON trace_competence');
        $this->addSql('ALTER TABLE trace_competence DROP portfolio_id');
    }
}
