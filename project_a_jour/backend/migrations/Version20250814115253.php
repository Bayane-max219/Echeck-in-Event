<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250814115253 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE agent ADD event_id INT NOT NULL');
        $this->addSql('ALTER TABLE agent ADD CONSTRAINT FK_268B9C9D71F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
        $this->addSql('ALTER TABLE agent ADD CONSTRAINT FK_268B9C9D7E3C61F9 FOREIGN KEY (owner_id) REFERENCES `user` (id)');
        $this->addSql('CREATE INDEX IDX_268B9C9D71F7E88B ON agent (event_id)');
        $this->addSql('CREATE INDEX IDX_268B9C9D7E3C61F9 ON agent (owner_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE agent DROP FOREIGN KEY FK_268B9C9D71F7E88B');
        $this->addSql('ALTER TABLE agent DROP FOREIGN KEY FK_268B9C9D7E3C61F9');
        $this->addSql('DROP INDEX IDX_268B9C9D71F7E88B ON agent');
        $this->addSql('DROP INDEX IDX_268B9C9D7E3C61F9 ON agent');
        $this->addSql('ALTER TABLE agent DROP event_id');
    }
}
