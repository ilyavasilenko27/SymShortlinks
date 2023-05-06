<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230402191352 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__link AS SELECT id, name, url, created FROM link');
        $this->addSql('DROP TABLE link');
        $this->addSql('CREATE TABLE link (id VARCHAR(255) NOT NULL, name VARCHAR(255) DEFAULT NULL, url VARCHAR(255) NOT NULL, created DATETIME NOT NULL)');
        $this->addSql('INSERT INTO link (id, name, url, created) SELECT id, name, url, created FROM __temp__link');
        $this->addSql('DROP TABLE __temp__link');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__link AS SELECT id, name, url, created FROM link');
        $this->addSql('DROP TABLE link');
        $this->addSql('CREATE TABLE link (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) DEFAULT NULL, url VARCHAR(255) NOT NULL, created DATETIME NOT NULL, token VARCHAR(255) NOT NULL)');
        $this->addSql('INSERT INTO link (id, name, url, created) SELECT id, name, url, created FROM __temp__link');
        $this->addSql('DROP TABLE __temp__link');
    }
}
