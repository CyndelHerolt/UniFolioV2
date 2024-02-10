<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240210175708 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE annee (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(255) NOT NULL, ordre INT NOT NULL, libelle_long VARCHAR(255) NOT NULL, opt_alternance TINYINT(1) NOT NULL, actif TINYINT(1) NOT NULL, diplome_id INT NOT NULL, INDEX IDX_DE92C5CF26F859E2 (diplome_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE annee_universitaire (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(30) NOT NULL, active TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE apc_apprentissage_critique (id INT AUTO_INCREMENT NOT NULL, libelle LONGTEXT NOT NULL, code VARCHAR(20) NOT NULL, apc_niveau_id INT DEFAULT NULL, INDEX IDX_A99B947A9445617E (apc_niveau_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE apc_competence (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(255) NOT NULL, nom_court VARCHAR(50) NOT NULL, couleur VARCHAR(20) NOT NULL, code VARCHAR(20) NOT NULL, ue VARCHAR(20) NOT NULL, apc_referentiel_id INT NOT NULL, INDEX IDX_B949FC0F9048A9AB (apc_referentiel_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE apc_niveau (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(255) NOT NULL, ordre INT NOT NULL, annee_id INT NOT NULL, apc_competence_id INT NOT NULL, INDEX IDX_5CE8A823543EC5F0 (annee_id), INDEX IDX_5CE8A823DA14D531 (apc_competence_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE apc_niveau_apc_parcours (apc_niveau_id INT NOT NULL, apc_parcours_id INT NOT NULL, INDEX IDX_496327F59445617E (apc_niveau_id), INDEX IDX_496327F53E102C73 (apc_parcours_id), PRIMARY KEY(apc_niveau_id, apc_parcours_id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE apc_parcours (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(255) NOT NULL, code VARCHAR(10) NOT NULL, actif TINYINT(1) NOT NULL, formation_continue TINYINT(1) NOT NULL, apc_referentiel_id INT NOT NULL, INDEX IDX_5DA884A59048A9AB (apc_referentiel_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE apc_referentiel (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, annee_publication INT NOT NULL, departement_id INT NOT NULL, INDEX IDX_E744CC81CCF9E01E (departement_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE bibliotheque (id INT AUTO_INCREMENT NOT NULL, etudiant_id INT DEFAULT NULL, annee_universitaire_id INT DEFAULT NULL, annee_id INT DEFAULT NULL, INDEX IDX_4690D34DDDEAB1A3 (etudiant_id), INDEX IDX_4690D34D544BFD58 (annee_universitaire_id), INDEX IDX_4690D34D543EC5F0 (annee_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE departement (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(255) NOT NULL, logo_name VARCHAR(50) DEFAULT NULL, couleur VARCHAR(16) DEFAULT NULL, description LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE departement_enseignant (id INT AUTO_INCREMENT NOT NULL, defaut TINYINT(1) NOT NULL, enseignant_id INT DEFAULT NULL, departement_id INT DEFAULT NULL, INDEX IDX_5F9BAFA9E455FCC0 (enseignant_id), INDEX IDX_5F9BAFA9CCF9E01E (departement_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE diplome (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(255) NOT NULL, sigle VARCHAR(40) NOT NULL, departement_id INT NOT NULL, INDEX IDX_EB4C4D4ECCF9E01E (departement_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE enseignant (id INT AUTO_INCREMENT NOT NULL, prenom VARCHAR(50) NOT NULL, nom VARCHAR(50) NOT NULL, mail_perso VARCHAR(255) DEFAULT NULL, mail_univ VARCHAR(255) NOT NULL, userame VARCHAR(75) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE etudiant (id INT AUTO_INCREMENT NOT NULL, prenom VARCHAR(50) NOT NULL, nom VARCHAR(50) NOT NULL, mail_perso VARCHAR(255) DEFAULT NULL, mail_univ VARCHAR(255) NOT NULL, bio LONGTEXT DEFAULT NULL, opt_alt_stage INT NOT NULL, annee_sortie INT DEFAULT NULL, username VARCHAR(75) NOT NULL, semestre_id INT NOT NULL, INDEX IDX_717E22E35577AFDB (semestre_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE etudiant_groupe (etudiant_id INT NOT NULL, groupe_id INT NOT NULL, INDEX IDX_10C7EA42DDEAB1A3 (etudiant_id), INDEX IDX_10C7EA427A45358C (groupe_id), PRIMARY KEY(etudiant_id, groupe_id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE groupe (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(255) NOT NULL, code_apogee VARCHAR(50) NOT NULL, ordre INT NOT NULL, type_groupe_id INT NOT NULL, apc_parcours_id INT DEFAULT NULL, INDEX IDX_4B98C21CE83749C (type_groupe_id), INDEX IDX_4B98C213E102C73 (apc_parcours_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE semestre (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(255) NOT NULL, ordre_annee INT NOT NULL, actif TINYINT(1) NOT NULL, ordre_lmd INT NOT NULL, code_element VARCHAR(20) NOT NULL, annee_id INT NOT NULL, INDEX IDX_71688FBC543EC5F0 (annee_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE type_groupe (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(50) NOT NULL, mutualise TINYINT(1) NOT NULL, ordre_semestre INT NOT NULL, type VARCHAR(2) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE type_groupe_semestre (type_groupe_id INT NOT NULL, semestre_id INT NOT NULL, INDEX IDX_17FE10B8CE83749C (type_groupe_id), INDEX IDX_17FE10B85577AFDB (semestre_id), PRIMARY KEY(type_groupe_id, semestre_id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, is_verified TINYINT(1) NOT NULL, etudiant_id INT DEFAULT NULL, enseignant_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649F85E0677 (username), UNIQUE INDEX UNIQ_8D93D649DDEAB1A3 (etudiant_id), UNIQUE INDEX UNIQ_8D93D649E455FCC0 (enseignant_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('ALTER TABLE annee ADD CONSTRAINT FK_DE92C5CF26F859E2 FOREIGN KEY (diplome_id) REFERENCES diplome (id)');
        $this->addSql('ALTER TABLE apc_apprentissage_critique ADD CONSTRAINT FK_A99B947A9445617E FOREIGN KEY (apc_niveau_id) REFERENCES apc_niveau (id)');
        $this->addSql('ALTER TABLE apc_competence ADD CONSTRAINT FK_B949FC0F9048A9AB FOREIGN KEY (apc_referentiel_id) REFERENCES apc_referentiel (id)');
        $this->addSql('ALTER TABLE apc_niveau ADD CONSTRAINT FK_5CE8A823543EC5F0 FOREIGN KEY (annee_id) REFERENCES annee (id)');
        $this->addSql('ALTER TABLE apc_niveau ADD CONSTRAINT FK_5CE8A823DA14D531 FOREIGN KEY (apc_competence_id) REFERENCES apc_competence (id)');
        $this->addSql('ALTER TABLE apc_niveau_apc_parcours ADD CONSTRAINT FK_496327F59445617E FOREIGN KEY (apc_niveau_id) REFERENCES apc_niveau (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE apc_niveau_apc_parcours ADD CONSTRAINT FK_496327F53E102C73 FOREIGN KEY (apc_parcours_id) REFERENCES apc_parcours (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE apc_parcours ADD CONSTRAINT FK_5DA884A59048A9AB FOREIGN KEY (apc_referentiel_id) REFERENCES apc_referentiel (id)');
        $this->addSql('ALTER TABLE apc_referentiel ADD CONSTRAINT FK_E744CC81CCF9E01E FOREIGN KEY (departement_id) REFERENCES departement (id)');
        $this->addSql('ALTER TABLE bibliotheque ADD CONSTRAINT FK_4690D34DDDEAB1A3 FOREIGN KEY (etudiant_id) REFERENCES etudiant (id)');
        $this->addSql('ALTER TABLE bibliotheque ADD CONSTRAINT FK_4690D34D544BFD58 FOREIGN KEY (annee_universitaire_id) REFERENCES annee_universitaire (id)');
        $this->addSql('ALTER TABLE bibliotheque ADD CONSTRAINT FK_4690D34D543EC5F0 FOREIGN KEY (annee_id) REFERENCES annee (id)');
        $this->addSql('ALTER TABLE departement_enseignant ADD CONSTRAINT FK_5F9BAFA9E455FCC0 FOREIGN KEY (enseignant_id) REFERENCES enseignant (id)');
        $this->addSql('ALTER TABLE departement_enseignant ADD CONSTRAINT FK_5F9BAFA9CCF9E01E FOREIGN KEY (departement_id) REFERENCES departement (id)');
        $this->addSql('ALTER TABLE diplome ADD CONSTRAINT FK_EB4C4D4ECCF9E01E FOREIGN KEY (departement_id) REFERENCES departement (id)');
        $this->addSql('ALTER TABLE etudiant ADD CONSTRAINT FK_717E22E35577AFDB FOREIGN KEY (semestre_id) REFERENCES semestre (id)');
        $this->addSql('ALTER TABLE etudiant_groupe ADD CONSTRAINT FK_10C7EA42DDEAB1A3 FOREIGN KEY (etudiant_id) REFERENCES etudiant (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE etudiant_groupe ADD CONSTRAINT FK_10C7EA427A45358C FOREIGN KEY (groupe_id) REFERENCES groupe (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE groupe ADD CONSTRAINT FK_4B98C21CE83749C FOREIGN KEY (type_groupe_id) REFERENCES type_groupe (id)');
        $this->addSql('ALTER TABLE groupe ADD CONSTRAINT FK_4B98C213E102C73 FOREIGN KEY (apc_parcours_id) REFERENCES apc_parcours (id)');
        $this->addSql('ALTER TABLE semestre ADD CONSTRAINT FK_71688FBC543EC5F0 FOREIGN KEY (annee_id) REFERENCES annee (id)');
        $this->addSql('ALTER TABLE type_groupe_semestre ADD CONSTRAINT FK_17FE10B8CE83749C FOREIGN KEY (type_groupe_id) REFERENCES type_groupe (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE type_groupe_semestre ADD CONSTRAINT FK_17FE10B85577AFDB FOREIGN KEY (semestre_id) REFERENCES semestre (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE `user` ADD CONSTRAINT FK_8D93D649DDEAB1A3 FOREIGN KEY (etudiant_id) REFERENCES etudiant (id)');
        $this->addSql('ALTER TABLE `user` ADD CONSTRAINT FK_8D93D649E455FCC0 FOREIGN KEY (enseignant_id) REFERENCES enseignant (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE annee DROP FOREIGN KEY FK_DE92C5CF26F859E2');
        $this->addSql('ALTER TABLE apc_apprentissage_critique DROP FOREIGN KEY FK_A99B947A9445617E');
        $this->addSql('ALTER TABLE apc_competence DROP FOREIGN KEY FK_B949FC0F9048A9AB');
        $this->addSql('ALTER TABLE apc_niveau DROP FOREIGN KEY FK_5CE8A823543EC5F0');
        $this->addSql('ALTER TABLE apc_niveau DROP FOREIGN KEY FK_5CE8A823DA14D531');
        $this->addSql('ALTER TABLE apc_niveau_apc_parcours DROP FOREIGN KEY FK_496327F59445617E');
        $this->addSql('ALTER TABLE apc_niveau_apc_parcours DROP FOREIGN KEY FK_496327F53E102C73');
        $this->addSql('ALTER TABLE apc_parcours DROP FOREIGN KEY FK_5DA884A59048A9AB');
        $this->addSql('ALTER TABLE apc_referentiel DROP FOREIGN KEY FK_E744CC81CCF9E01E');
        $this->addSql('ALTER TABLE bibliotheque DROP FOREIGN KEY FK_4690D34DDDEAB1A3');
        $this->addSql('ALTER TABLE bibliotheque DROP FOREIGN KEY FK_4690D34D544BFD58');
        $this->addSql('ALTER TABLE bibliotheque DROP FOREIGN KEY FK_4690D34D543EC5F0');
        $this->addSql('ALTER TABLE departement_enseignant DROP FOREIGN KEY FK_5F9BAFA9E455FCC0');
        $this->addSql('ALTER TABLE departement_enseignant DROP FOREIGN KEY FK_5F9BAFA9CCF9E01E');
        $this->addSql('ALTER TABLE diplome DROP FOREIGN KEY FK_EB4C4D4ECCF9E01E');
        $this->addSql('ALTER TABLE etudiant DROP FOREIGN KEY FK_717E22E35577AFDB');
        $this->addSql('ALTER TABLE etudiant_groupe DROP FOREIGN KEY FK_10C7EA42DDEAB1A3');
        $this->addSql('ALTER TABLE etudiant_groupe DROP FOREIGN KEY FK_10C7EA427A45358C');
        $this->addSql('ALTER TABLE groupe DROP FOREIGN KEY FK_4B98C21CE83749C');
        $this->addSql('ALTER TABLE groupe DROP FOREIGN KEY FK_4B98C213E102C73');
        $this->addSql('ALTER TABLE semestre DROP FOREIGN KEY FK_71688FBC543EC5F0');
        $this->addSql('ALTER TABLE type_groupe_semestre DROP FOREIGN KEY FK_17FE10B8CE83749C');
        $this->addSql('ALTER TABLE type_groupe_semestre DROP FOREIGN KEY FK_17FE10B85577AFDB');
        $this->addSql('ALTER TABLE `user` DROP FOREIGN KEY FK_8D93D649DDEAB1A3');
        $this->addSql('ALTER TABLE `user` DROP FOREIGN KEY FK_8D93D649E455FCC0');
        $this->addSql('DROP TABLE annee');
        $this->addSql('DROP TABLE annee_universitaire');
        $this->addSql('DROP TABLE apc_apprentissage_critique');
        $this->addSql('DROP TABLE apc_competence');
        $this->addSql('DROP TABLE apc_niveau');
        $this->addSql('DROP TABLE apc_niveau_apc_parcours');
        $this->addSql('DROP TABLE apc_parcours');
        $this->addSql('DROP TABLE apc_referentiel');
        $this->addSql('DROP TABLE bibliotheque');
        $this->addSql('DROP TABLE departement');
        $this->addSql('DROP TABLE departement_enseignant');
        $this->addSql('DROP TABLE diplome');
        $this->addSql('DROP TABLE enseignant');
        $this->addSql('DROP TABLE etudiant');
        $this->addSql('DROP TABLE etudiant_groupe');
        $this->addSql('DROP TABLE groupe');
        $this->addSql('DROP TABLE semestre');
        $this->addSql('DROP TABLE type_groupe');
        $this->addSql('DROP TABLE type_groupe_semestre');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
