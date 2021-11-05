<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211104231127 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE invitation (
            id INT AUTO_INCREMENT NOT NULL, 
            sender_id INT NOT NULL, 
            status SMALLINT NOT NULL, 
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, 
            guest_email VARCHAR(255) NOT NULL, 
            INDEX IDX_F11D61A2F624B39D (sender_id), 
            PRIMARY KEY(id)) 
            DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB
        ');

        $this->addSql('CREATE TABLE `user` (
            id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, 
            username_canonical VARCHAR(180) NOT NULL, 
            email VARCHAR(180) NOT NULL, 
            email_canonical VARCHAR(180) NOT NULL, 
            enabled TINYINT(1) NOT NULL, 
            salt VARCHAR(255) DEFAULT NULL, 
            password VARCHAR(255) NOT NULL, 
            last_login DATETIME DEFAULT NULL, 
            confirmation_token VARCHAR(180) DEFAULT NULL,
            password_requested_at DATETIME DEFAULT NULL, 
            roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', 
            UNIQUE INDEX UNIQ_8D93D64992FC23A8 (username_canonical), 
            UNIQUE INDEX UNIQ_8D93D649A0D96FBF (email_canonical), 
            UNIQUE INDEX UNIQ_8D93D649C05FB297 (confirmation_token), 
            PRIMARY KEY(id)) 
            DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB'
        );

        $this->addSql('ALTER TABLE invitation ADD CONSTRAINT FK_F11D61A2F624B39D FOREIGN KEY (sender_id) REFERENCES `user` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE invitation DROP FOREIGN KEY FK_F11D61A2F624B39D');
        $this->addSql('DROP TABLE invitation');
        $this->addSql('DROP TABLE `user`');
    }
}
