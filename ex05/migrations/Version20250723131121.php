<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250723131121 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE post (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, created DATETIME NOT NULL, author_id INT NOT NULL, INDEX IDX_5A8A6C8DF675F31B (author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649F85E0677 (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE vote (id INT AUTO_INCREMENT NOT NULL, is_like TINYINT(1) NOT NULL, user_id INT NOT NULL, post_id INT NOT NULL, INDEX IDX_5A108564A76ED395 (user_id), INDEX IDX_5A1085644B89032C (post_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8DF675F31B FOREIGN KEY (author_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE vote ADD CONSTRAINT FK_5A108564A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE vote ADD CONSTRAINT FK_5A1085644B89032C FOREIGN KEY (post_id) REFERENCES post (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8DF675F31B');
        $this->addSql('ALTER TABLE vote DROP FOREIGN KEY FK_5A108564A76ED395');
        $this->addSql('ALTER TABLE vote DROP FOREIGN KEY FK_5A1085644B89032C');
        $this->addSql('DROP TABLE post');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE vote');
    }
}
