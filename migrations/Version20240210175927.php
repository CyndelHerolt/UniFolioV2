<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240210175927 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE diplome ADD apc_parcours_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE diplome ADD CONSTRAINT FK_EB4C4D4E3E102C73 FOREIGN KEY (apc_parcours_id) REFERENCES apc_parcours (id)');
        $this->addSql('CREATE INDEX IDX_EB4C4D4E3E102C73 ON diplome (apc_parcours_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE diplome DROP FOREIGN KEY FK_EB4C4D4E3E102C73');
        $this->addSql('DROP INDEX IDX_EB4C4D4E3E102C73 ON diplome');
        $this->addSql('ALTER TABLE diplome DROP apc_parcours_id');
    }
}
