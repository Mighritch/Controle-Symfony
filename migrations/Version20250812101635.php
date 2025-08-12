<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250812101635 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add only new tables that don\'t exist yet';
    }

    public function up(Schema $schema): void
    {
        // Check if tables exist before creating them
        if (!$schema->hasTable('categorie')) {
            $this->addSql('CREATE TABLE categorie (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        }

        if (!$schema->hasTable('reservation')) {
            $this->addSql('CREATE TABLE reservation (id INT AUTO_INCREMENT NOT NULL, nombreplaces INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        }

        // Don't recreate tables that already exist (event, user, messenger_messages)
        // They were created by previous migrations
    }

    public function down(Schema $schema): void
    {
        // Only drop tables that this migration would have created
        $this->addSql('DROP TABLE IF EXISTS categorie');
        $this->addSql('DROP TABLE IF EXISTS reservation');
        
        // Don't drop tables that existed before this migration
    }
}