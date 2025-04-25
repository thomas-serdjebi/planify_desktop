<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
<<<<<<<< HEAD:migrations/Version20250416074931.php
final class Version20250416074931 extends AbstractMigration
========
final class Version20250219113553 extends AbstractMigration
>>>>>>>> origin/devThomas14022025:migrations/Version20250219113553.php
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
<<<<<<<< HEAD:migrations/Version20250416074931.php
        $this->addSql('CREATE TABLE historique (id INT AUTO_INCREMENT NOT NULL, livraison_id INT NOT NULL, contenu VARCHAR(255) NOT NULL, date DATETIME NOT NULL, INDEX IDX_EDBFD5EC8E54FB25 (livraison_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE livraison (id INT AUTO_INCREMENT NOT NULL, trajet_id INT DEFAULT NULL, tournee_id INT DEFAULT NULL, numero VARCHAR(20) NOT NULL, adresse VARCHAR(255) NOT NULL, code_postal VARCHAR(10) NOT NULL, ville VARCHAR(50) DEFAULT NULL, client_nom VARCHAR(50) NOT NULL, client_prenom VARCHAR(50) NOT NULL, client_email VARCHAR(255) NOT NULL, client_telephone VARCHAR(20) NOT NULL, date DATE NOT NULL, creneau VARCHAR(1) DEFAULT NULL, statut VARCHAR(20) NOT NULL, longitude DOUBLE PRECISION NOT NULL, latitude DOUBLE PRECISION NOT NULL, UNIQUE INDEX UNIQ_A60C9F1FD12A823 (trajet_id), INDEX IDX_A60C9F1FF661D013 (tournee_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
========
        $this->addSql('CREATE TABLE historique (id INT AUTO_INCREMENT NOT NULL, livraison_id INT NOT NULL, date_enregistrement DATETIME NOT NULL, evenement VARCHAR(100) NOT NULL, numero VARCHAR(20) NOT NULL, adresse VARCHAR(255) NOT NULL, code_postal VARCHAR(10) NOT NULL, ville VARCHAR(50) DEFAULT NULL, client_nom VARCHAR(50) NOT NULL, client_prenom VARCHAR(50) NOT NULL, client_email VARCHAR(255) NOT NULL, client_telephone VARCHAR(20) NOT NULL, date DATE NOT NULL, creneau VARCHAR(1) DEFAULT NULL, statut VARCHAR(20) NOT NULL, INDEX IDX_EDBFD5EC8E54FB25 (livraison_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE livraison (id INT AUTO_INCREMENT NOT NULL, trajet_id INT DEFAULT NULL, tournee_id INT NOT NULL, numero VARCHAR(20) NOT NULL, adresse VARCHAR(255) NOT NULL, code_postal VARCHAR(10) NOT NULL, ville VARCHAR(50) DEFAULT NULL, client_nom VARCHAR(50) NOT NULL, client_prenom VARCHAR(50) NOT NULL, client_email VARCHAR(255) NOT NULL, client_telephone VARCHAR(20) NOT NULL, date DATE NOT NULL, creneau VARCHAR(1) DEFAULT NULL, statut VARCHAR(20) NOT NULL, latitude DOUBLE PRECISION DEFAULT NULL, longitude DOUBLE PRECISION DEFAULT NULL, UNIQUE INDEX UNIQ_A60C9F1FD12A823 (trajet_id), INDEX IDX_A60C9F1FF661D013 (tournee_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
>>>>>>>> origin/devThomas14022025:migrations/Version20250219113553.php
        $this->addSql('CREATE TABLE tournee (id INT AUTO_INCREMENT NOT NULL, livreur_id INT DEFAULT NULL, date DATE NOT NULL, duree TIME NOT NULL, distance DOUBLE PRECISION NOT NULL, statut VARCHAR(50) NOT NULL, creneau VARCHAR(1) NOT NULL, INDEX IDX_EBF67D7EF8646701 (livreur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE trajet (id INT AUTO_INCREMENT NOT NULL, tournee_id INT DEFAULT NULL, distance DOUBLE PRECISION NOT NULL, duree TIME NOT NULL, ordre INT DEFAULT NULL, INDEX IDX_2B5BA98CF661D013 (tournee_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, name VARCHAR(50) NOT NULL, first_name VARCHAR(50) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE historique ADD CONSTRAINT FK_EDBFD5EC8E54FB25 FOREIGN KEY (livraison_id) REFERENCES livraison (id)');
        $this->addSql('ALTER TABLE livraison ADD CONSTRAINT FK_A60C9F1FD12A823 FOREIGN KEY (trajet_id) REFERENCES trajet (id)');
        $this->addSql('ALTER TABLE livraison ADD CONSTRAINT FK_A60C9F1FF661D013 FOREIGN KEY (tournee_id) REFERENCES tournee (id)');
        $this->addSql('ALTER TABLE tournee ADD CONSTRAINT FK_EBF67D7EF8646701 FOREIGN KEY (livreur_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE trajet ADD CONSTRAINT FK_2B5BA98CF661D013 FOREIGN KEY (tournee_id) REFERENCES tournee (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE historique DROP FOREIGN KEY FK_EDBFD5EC8E54FB25');
        $this->addSql('ALTER TABLE livraison DROP FOREIGN KEY FK_A60C9F1FD12A823');
        $this->addSql('ALTER TABLE livraison DROP FOREIGN KEY FK_A60C9F1FF661D013');
        $this->addSql('ALTER TABLE tournee DROP FOREIGN KEY FK_EBF67D7EF8646701');
        $this->addSql('ALTER TABLE trajet DROP FOREIGN KEY FK_2B5BA98CF661D013');
        $this->addSql('DROP TABLE historique');
        $this->addSql('DROP TABLE livraison');
        $this->addSql('DROP TABLE tournee');
        $this->addSql('DROP TABLE trajet');
        $this->addSql('DROP TABLE `user`');
    }
}
