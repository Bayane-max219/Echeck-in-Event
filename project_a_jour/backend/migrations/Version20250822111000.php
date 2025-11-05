<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250822111000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout du champ menu (texte nullable) à la table event';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE event ADD menu LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE event DROP menu');
    }
}
