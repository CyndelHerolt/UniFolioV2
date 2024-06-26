<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240626135759 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE page ADD apc_apprentissage_critique_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE page ADD CONSTRAINT FK_140AB6204DD72DCD FOREIGN KEY (apc_apprentissage_critique_id) REFERENCES apc_apprentissage_critique (id)');
        $this->addSql('CREATE INDEX IDX_140AB6204DD72DCD ON page (apc_apprentissage_critique_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE page DROP FOREIGN KEY FK_140AB6204DD72DCD');
        $this->addSql('DROP INDEX IDX_140AB6204DD72DCD ON page');
        $this->addSql('ALTER TABLE page DROP apc_apprentissage_critique_id');
    }
}
