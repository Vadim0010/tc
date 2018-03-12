<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170806205756 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE course (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, number_lessons INT DEFAULT NULL, duration_lessons INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `groups` (id INT AUTO_INCREMENT NOT NULL, course_id INT DEFAULT NULL, teacher_id INT DEFAULT NULL, curator_id INT DEFAULT NULL, number VARCHAR(50) NOT NULL, title VARCHAR(255) NOT NULL, days_lessons TINYTEXT DEFAULT NULL COMMENT \'(DC2Type:json_array)\', created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_F06D397096901F54 (number), INDEX IDX_F06D3970591CC992 (course_id), INDEX IDX_F06D397041807E1D (teacher_id), INDEX IDX_F06D3970733D5B5D (curator_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE groups_users (groups_id INT NOT NULL, users_id INT NOT NULL, INDEX IDX_4520C24DF373DCF (groups_id), INDEX IDX_4520C24D67B3B43D (users_id), PRIMARY KEY(groups_id, users_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE home_assignment (id INT AUTO_INCREMENT NOT NULL, course_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, body LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, INDEX IDX_410A154A591CC992 (course_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE home_assignment_files (id INT AUTO_INCREMENT NOT NULL, home_assignment_id INT DEFAULT NULL, path VARCHAR(500) NOT NULL, INDEX IDX_942B9AAECC2CC26D (home_assignment_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE lessons (id INT AUTO_INCREMENT NOT NULL, course_id INT NOT NULL, title VARCHAR(255) NOT NULL, body LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, INDEX IDX_3F4218D9591CC992 (course_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE lessons_users (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, lesson_id INT DEFAULT NULL, group_id INT DEFAULT NULL, home_assignment_id INT DEFAULT NULL, mark INT DEFAULT NULL, is_attend TINYINT(1) NOT NULL, comment LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, INDEX IDX_E530EACAA76ED395 (user_id), INDEX IDX_E530EACACDF80196 (lesson_id), INDEX IDX_E530EACAFE54D947 (group_id), INDEX IDX_E530EACACC2CC26D (home_assignment_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `users` (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, username VARCHAR(180) NOT NULL, username_canonical VARCHAR(180) NOT NULL, email VARCHAR(180) NOT NULL, email_canonical VARCHAR(180) NOT NULL, enabled TINYINT(1) NOT NULL, salt VARCHAR(255) DEFAULT NULL, password VARCHAR(255) NOT NULL, last_login DATETIME DEFAULT NULL, confirmation_token VARCHAR(180) DEFAULT NULL, password_requested_at DATETIME DEFAULT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', first_name VARCHAR(255) DEFAULT NULL, last_name VARCHAR(255) DEFAULT NULL, middle_name VARCHAR(255) DEFAULT NULL, phone VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_1483A5E992FC23A8 (username_canonical), UNIQUE INDEX UNIQ_1483A5E9A0D96FBF (email_canonical), UNIQUE INDEX UNIQ_1483A5E9C05FB297 (confirmation_token), INDEX IDX_1483A5E9727ACA70 (parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE `groups` ADD CONSTRAINT FK_F06D3970591CC992 FOREIGN KEY (course_id) REFERENCES course (id)');
        $this->addSql('ALTER TABLE `groups` ADD CONSTRAINT FK_F06D397041807E1D FOREIGN KEY (teacher_id) REFERENCES `users` (id)');
        $this->addSql('ALTER TABLE `groups` ADD CONSTRAINT FK_F06D3970733D5B5D FOREIGN KEY (curator_id) REFERENCES `users` (id)');
        $this->addSql('ALTER TABLE groups_users ADD CONSTRAINT FK_4520C24DF373DCF FOREIGN KEY (groups_id) REFERENCES `groups` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE groups_users ADD CONSTRAINT FK_4520C24D67B3B43D FOREIGN KEY (users_id) REFERENCES `users` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE home_assignment ADD CONSTRAINT FK_410A154A591CC992 FOREIGN KEY (course_id) REFERENCES course (id)');
        $this->addSql('ALTER TABLE home_assignment_files ADD CONSTRAINT FK_942B9AAECC2CC26D FOREIGN KEY (home_assignment_id) REFERENCES home_assignment (id)');
        $this->addSql('ALTER TABLE lessons ADD CONSTRAINT FK_3F4218D9591CC992 FOREIGN KEY (course_id) REFERENCES course (id)');
        $this->addSql('ALTER TABLE lessons_users ADD CONSTRAINT FK_E530EACAA76ED395 FOREIGN KEY (user_id) REFERENCES `users` (id)');
        $this->addSql('ALTER TABLE lessons_users ADD CONSTRAINT FK_E530EACACDF80196 FOREIGN KEY (lesson_id) REFERENCES lessons (id)');
        $this->addSql('ALTER TABLE lessons_users ADD CONSTRAINT FK_E530EACAFE54D947 FOREIGN KEY (group_id) REFERENCES `groups` (id)');
        $this->addSql('ALTER TABLE lessons_users ADD CONSTRAINT FK_E530EACACC2CC26D FOREIGN KEY (home_assignment_id) REFERENCES home_assignment (id)');
        $this->addSql('ALTER TABLE `users` ADD CONSTRAINT FK_1483A5E9727ACA70 FOREIGN KEY (parent_id) REFERENCES `users` (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE `groups` DROP FOREIGN KEY FK_F06D3970591CC992');
        $this->addSql('ALTER TABLE home_assignment DROP FOREIGN KEY FK_410A154A591CC992');
        $this->addSql('ALTER TABLE lessons DROP FOREIGN KEY FK_3F4218D9591CC992');
        $this->addSql('ALTER TABLE groups_users DROP FOREIGN KEY FK_4520C24DF373DCF');
        $this->addSql('ALTER TABLE lessons_users DROP FOREIGN KEY FK_E530EACAFE54D947');
        $this->addSql('ALTER TABLE home_assignment_files DROP FOREIGN KEY FK_942B9AAECC2CC26D');
        $this->addSql('ALTER TABLE lessons_users DROP FOREIGN KEY FK_E530EACACC2CC26D');
        $this->addSql('ALTER TABLE lessons_users DROP FOREIGN KEY FK_E530EACACDF80196');
        $this->addSql('ALTER TABLE `groups` DROP FOREIGN KEY FK_F06D397041807E1D');
        $this->addSql('ALTER TABLE `groups` DROP FOREIGN KEY FK_F06D3970733D5B5D');
        $this->addSql('ALTER TABLE groups_users DROP FOREIGN KEY FK_4520C24D67B3B43D');
        $this->addSql('ALTER TABLE lessons_users DROP FOREIGN KEY FK_E530EACAA76ED395');
        $this->addSql('ALTER TABLE `users` DROP FOREIGN KEY FK_1483A5E9727ACA70');
        $this->addSql('DROP TABLE course');
        $this->addSql('DROP TABLE `groups`');
        $this->addSql('DROP TABLE groups_users');
        $this->addSql('DROP TABLE home_assignment');
        $this->addSql('DROP TABLE home_assignment_files');
        $this->addSql('DROP TABLE lessons');
        $this->addSql('DROP TABLE lessons_users');
        $this->addSql('DROP TABLE `users`');
    }
}
