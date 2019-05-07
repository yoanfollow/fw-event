<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190507165616 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE place CHANGE name name VARCHAR(175) NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE email email VARCHAR(175) NOT NULL, CHANGE username username VARCHAR(175) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX uq_email_idx ON user (email)');
        $this->addSql('CREATE UNIQUE INDEX uq_username_idx ON user (username)');
        $this->addSql('CREATE UNIQUE INDEX uq_name_idx ON place (name)');
        $this->addSql('CREATE UNIQUE INDEX uq_event_recipient_idx ON invitation (event_id, recipient_id)');
        $this->addSql('CREATE UNIQUE INDEX uq_author_event_idx ON comment (author_id, event_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX uq_author_event_idx ON comment');
        $this->addSql('DROP INDEX uq_event_recipient_idx ON invitation');
        $this->addSql('DROP INDEX uq_name_idx ON place');
        $this->addSql('DROP INDEX uq_username_idx ON user');
        $this->addSql('DROP INDEX uq_email_idx ON user');
        $this->addSql('ALTER TABLE place CHANGE name name VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE user CHANGE email email VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, CHANGE username username VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci');
    }
}
