<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240626135721 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE page ADD apc_niveau_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE page ADD CONSTRAINT FK_140AB6209445617E FOREIGN KEY (apc_niveau_id) REFERENCES apc_niveau (id)');
        $this->addSql('CREATE INDEX IDX_140AB6209445617E ON page (apc_niveau_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE page DROP FOREIGN KEY FK_140AB6209445617E');
        $this->addSql('DROP INDEX IDX_140AB6209445617E ON page');
        $this->addSql('ALTER TABLE page DROP apc_niveau_id');
    }
}
