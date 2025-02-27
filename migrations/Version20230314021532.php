<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230314021532 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE paystatus ADD user_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE paystatus ADD CONSTRAINT FK_B9D92619A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('CREATE INDEX IDX_B9D92619A76ED395 ON paystatus (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE paystatus DROP FOREIGN KEY FK_B9D92619A76ED395');
        $this->addSql('DROP INDEX IDX_B9D92619A76ED395 ON paystatus');
        $this->addSql('ALTER TABLE paystatus DROP user_id');
    }
}
