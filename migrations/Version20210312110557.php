<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210312110557 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_bookmarks (user_id INT NOT NULL, tutorial_id INT NOT NULL, INDEX IDX_78324CA4A76ED395 (user_id), INDEX IDX_78324CA489366B7B (tutorial_id), PRIMARY KEY(user_id, tutorial_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_bookmarks ADD CONSTRAINT FK_78324CA4A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_bookmarks ADD CONSTRAINT FK_78324CA489366B7B FOREIGN KEY (tutorial_id) REFERENCES tutorial (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tutorial CHANGE read_time read_time INT NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE user_bookmarks');
        $this->addSql('ALTER TABLE tutorial CHANGE read_time read_time INT DEFAULT 0 NOT NULL');
    }
}
