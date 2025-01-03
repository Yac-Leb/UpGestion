<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250103113029 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE classement ADD nom VARCHAR(255) NOT NULL, ADD logo VARCHAR(255) NOT NULL, ADD points INT NOT NULL, ADD description LONGTEXT DEFAULT NULL, DROP classement');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE classement ADD classement JSON NOT NULL COMMENT \'(DC2Type:json)\', DROP nom, DROP logo, DROP points, DROP description');
    }
}
