<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240217134431 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE trace ADD date_creation DATE NOT NULL, ADD date_modification DATE DEFAULT NULL, ADD type VARCHAR(255) NOT NULL, ADD libelle VARCHAR(100) NOT NULL, ADD contenu JSON NOT NULL, ADD description LONGTEXT NOT NULL, ADD legende VARCHAR(100) DEFAULT NULL, ADD date_realisation DATE DEFAULT NULL, ADD contexte VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE trace DROP date_creation, DROP date_modification, DROP type, DROP libelle, DROP contenu, DROP description, DROP legende, DROP date_realisation, DROP contexte');
    }
}
