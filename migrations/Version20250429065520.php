<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250429065520 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE historique ADD date_enregistrement DATETIME NOT NULL, ADD evenement VARCHAR(100) NOT NULL, ADD numero VARCHAR(20) NOT NULL, ADD code_postal VARCHAR(10) NOT NULL, ADD ville VARCHAR(50) DEFAULT NULL, ADD client_nom VARCHAR(50) NOT NULL, ADD client_prenom VARCHAR(50) NOT NULL, ADD client_email VARCHAR(255) NOT NULL, ADD client_telephone VARCHAR(20) NOT NULL, ADD creneau VARCHAR(1) DEFAULT NULL, ADD statut VARCHAR(20) NOT NULL, CHANGE date date DATE NOT NULL, CHANGE contenu adresse VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE historique ADD contenu VARCHAR(255) NOT NULL, DROP date_enregistrement, DROP evenement, DROP numero, DROP adresse, DROP code_postal, DROP ville, DROP client_nom, DROP client_prenom, DROP client_email, DROP client_telephone, DROP creneau, DROP statut, CHANGE date date DATETIME NOT NULL');
    }
}
