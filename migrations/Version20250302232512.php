<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250302232512 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE tasks (id INT UNSIGNED AUTO_INCREMENT NOT NULL, job_id INT UNSIGNED DEFAULT NULL, user_id INT UNSIGNED DEFAULT NULL, taskComment VARCHAR(255) DEFAULT NULL, pendingComment VARCHAR(255) DEFAULT NULL, inprogressComment VARCHAR(255) DEFAULT NULL, completedComment VARCHAR(255) DEFAULT NULL, deadline DATETIME DEFAULT NULL, inprogressDate DATETIME DEFAULT NULL, completedDate DATETIME DEFAULT NULL, taskStatus VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, assignedTo_id INT UNSIGNED DEFAULT NULL, INDEX IDX_50586597BE04EA9 (job_id), INDEX IDX_50586597A76ED395 (user_id), INDEX IDX_5058659745A9EF83 (assignedTo_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE tasks ADD CONSTRAINT FK_50586597BE04EA9 FOREIGN KEY (job_id) REFERENCES jobs (id)');
        $this->addSql('ALTER TABLE tasks ADD CONSTRAINT FK_50586597A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE tasks ADD CONSTRAINT FK_5058659745A9EF83 FOREIGN KEY (assignedTo_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE admins');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE admins (id INT UNSIGNED AUTO_INCREMENT NOT NULL, `admin` VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, addedBy VARCHAR(255) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_unicode_ci`, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE tasks DROP FOREIGN KEY FK_50586597BE04EA9');
        $this->addSql('ALTER TABLE tasks DROP FOREIGN KEY FK_50586597A76ED395');
        $this->addSql('ALTER TABLE tasks DROP FOREIGN KEY FK_5058659745A9EF83');
        $this->addSql('DROP TABLE tasks');
    }
}
