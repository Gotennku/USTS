<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250903131555 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE admin_config (id INT AUTO_INCREMENT NOT NULL, `key` VARCHAR(100) NOT NULL, value LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE audit_log (id INT AUTO_INCREMENT NOT NULL, action VARCHAR(150) NOT NULL, actor VARCHAR(100) NOT NULL, date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE auth_token (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, token VARCHAR(255) NOT NULL, expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_9315F04E5F37A13B (token), INDEX IDX_9315F04EA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE authorized_app (id INT AUTO_INCREMENT NOT NULL, child_id INT NOT NULL, app_name VARCHAR(150) NOT NULL, package_name VARCHAR(200) NOT NULL, is_allowed TINYINT(1) NOT NULL, INDEX IDX_42266CEFDD62C21B (child_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE authorized_contact (id INT AUTO_INCREMENT NOT NULL, child_id INT NOT NULL, name VARCHAR(150) NOT NULL, phone_number VARCHAR(30) NOT NULL, relation VARCHAR(50) NOT NULL, INDEX IDX_BBA68CB7DD62C21B (child_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE back_office_stat (id INT AUTO_INCREMENT NOT NULL, metric VARCHAR(100) NOT NULL, value DOUBLE PRECISION NOT NULL, date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ban_list (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, reason VARCHAR(255) NOT NULL, banned_until DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_371C2ECAA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE emergency_call (id INT AUTO_INCREMENT NOT NULL, child_id INT NOT NULL, timestamp DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', status VARCHAR(255) NOT NULL, INDEX IDX_A7F119ABDD62C21B (child_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE emergency_contact (id INT AUTO_INCREMENT NOT NULL, child_id INT NOT NULL, name VARCHAR(150) NOT NULL, phone_number VARCHAR(30) NOT NULL, INDEX IDX_FE1C6190DD62C21B (child_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE geo_location (id INT AUTO_INCREMENT NOT NULL, child_id INT NOT NULL, latitude DOUBLE PRECISION NOT NULL, longitude DOUBLE PRECISION NOT NULL, timestamp DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_B027FE6ADD62C21B (child_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE parental_settings (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, pin_code VARCHAR(10) NOT NULL, safe_mode TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_388299C9A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE password_reset_token (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, token VARCHAR(255) NOT NULL, expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', used TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_6B7BA4B65F37A13B (token), INDEX IDX_6B7BA4B6A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE stripe_webhook_log (id INT AUTO_INCREMENT NOT NULL, subscription_id INT DEFAULT NULL, event_type VARCHAR(100) NOT NULL, payload JSON NOT NULL COMMENT \'(DC2Type:json)\', received_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', processed TINYINT(1) NOT NULL, INDEX IDX_907D47CC9A1887DC (subscription_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE subscription_history (id INT AUTO_INCREMENT NOT NULL, subscription_id INT NOT NULL, status VARCHAR(255) NOT NULL, changed_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', event VARCHAR(255) NOT NULL, INDEX IDX_54AF90D09A1887DC (subscription_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE subscription_plan (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, duration_days INT NOT NULL, price NUMERIC(10, 2) NOT NULL, currency VARCHAR(10) NOT NULL, description LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_log (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, ip_address VARCHAR(45) NOT NULL, device VARCHAR(100) NOT NULL, action VARCHAR(100) NOT NULL, timestamp DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_6429094EA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE auth_token ADD CONSTRAINT FK_9315F04EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE authorized_app ADD CONSTRAINT FK_42266CEFDD62C21B FOREIGN KEY (child_id) REFERENCES `child` (id)');
        $this->addSql('ALTER TABLE authorized_contact ADD CONSTRAINT FK_BBA68CB7DD62C21B FOREIGN KEY (child_id) REFERENCES `child` (id)');
        $this->addSql('ALTER TABLE ban_list ADD CONSTRAINT FK_371C2ECAA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE emergency_call ADD CONSTRAINT FK_A7F119ABDD62C21B FOREIGN KEY (child_id) REFERENCES `child` (id)');
        $this->addSql('ALTER TABLE emergency_contact ADD CONSTRAINT FK_FE1C6190DD62C21B FOREIGN KEY (child_id) REFERENCES `child` (id)');
        $this->addSql('ALTER TABLE geo_location ADD CONSTRAINT FK_B027FE6ADD62C21B FOREIGN KEY (child_id) REFERENCES `child` (id)');
        $this->addSql('ALTER TABLE parental_settings ADD CONSTRAINT FK_388299C9A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE password_reset_token ADD CONSTRAINT FK_6B7BA4B6A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE stripe_webhook_log ADD CONSTRAINT FK_907D47CC9A1887DC FOREIGN KEY (subscription_id) REFERENCES subscription (id)');
        $this->addSql('ALTER TABLE subscription_history ADD CONSTRAINT FK_54AF90D09A1887DC FOREIGN KEY (subscription_id) REFERENCES subscription (id)');
        $this->addSql('ALTER TABLE user_log ADD CONSTRAINT FK_6429094EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840DA76ED395');
        $this->addSql('DROP INDEX IDX_6D28840DA76ED395 ON payment');
        $this->addSql('ALTER TABLE payment CHANGE status status VARCHAR(255) NOT NULL, CHANGE user_id subscription_id INT NOT NULL');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840D9A1887DC FOREIGN KEY (subscription_id) REFERENCES subscription (id)');
        $this->addSql('CREATE INDEX IDX_6D28840D9A1887DC ON payment (subscription_id)');
        $this->addSql('ALTER TABLE subscription DROP INDEX IDX_A3C664D3A76ED395, ADD UNIQUE INDEX UNIQ_A3C664D3A76ED395 (user_id)');
        $this->addSql('ALTER TABLE subscription ADD plan_id INT DEFAULT NULL, CHANGE status status VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE subscription ADD CONSTRAINT FK_A3C664D3E899029B FOREIGN KEY (plan_id) REFERENCES subscription_plan (id)');
        $this->addSql('CREATE INDEX IDX_A3C664D3E899029B ON subscription (plan_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE subscription DROP FOREIGN KEY FK_A3C664D3E899029B');
        $this->addSql('ALTER TABLE auth_token DROP FOREIGN KEY FK_9315F04EA76ED395');
        $this->addSql('ALTER TABLE authorized_app DROP FOREIGN KEY FK_42266CEFDD62C21B');
        $this->addSql('ALTER TABLE authorized_contact DROP FOREIGN KEY FK_BBA68CB7DD62C21B');
        $this->addSql('ALTER TABLE ban_list DROP FOREIGN KEY FK_371C2ECAA76ED395');
        $this->addSql('ALTER TABLE emergency_call DROP FOREIGN KEY FK_A7F119ABDD62C21B');
        $this->addSql('ALTER TABLE emergency_contact DROP FOREIGN KEY FK_FE1C6190DD62C21B');
        $this->addSql('ALTER TABLE geo_location DROP FOREIGN KEY FK_B027FE6ADD62C21B');
        $this->addSql('ALTER TABLE parental_settings DROP FOREIGN KEY FK_388299C9A76ED395');
        $this->addSql('ALTER TABLE password_reset_token DROP FOREIGN KEY FK_6B7BA4B6A76ED395');
        $this->addSql('ALTER TABLE stripe_webhook_log DROP FOREIGN KEY FK_907D47CC9A1887DC');
        $this->addSql('ALTER TABLE subscription_history DROP FOREIGN KEY FK_54AF90D09A1887DC');
        $this->addSql('ALTER TABLE user_log DROP FOREIGN KEY FK_6429094EA76ED395');
        $this->addSql('DROP TABLE admin_config');
        $this->addSql('DROP TABLE audit_log');
        $this->addSql('DROP TABLE auth_token');
        $this->addSql('DROP TABLE authorized_app');
        $this->addSql('DROP TABLE authorized_contact');
        $this->addSql('DROP TABLE back_office_stat');
        $this->addSql('DROP TABLE ban_list');
        $this->addSql('DROP TABLE emergency_call');
        $this->addSql('DROP TABLE emergency_contact');
        $this->addSql('DROP TABLE geo_location');
        $this->addSql('DROP TABLE parental_settings');
        $this->addSql('DROP TABLE password_reset_token');
        $this->addSql('DROP TABLE stripe_webhook_log');
        $this->addSql('DROP TABLE subscription_history');
        $this->addSql('DROP TABLE subscription_plan');
        $this->addSql('DROP TABLE user_log');
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840D9A1887DC');
        $this->addSql('DROP INDEX IDX_6D28840D9A1887DC ON payment');
        $this->addSql('ALTER TABLE payment CHANGE status status VARCHAR(20) NOT NULL, CHANGE subscription_id user_id INT NOT NULL');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_6D28840DA76ED395 ON payment (user_id)');
        $this->addSql('ALTER TABLE subscription DROP INDEX UNIQ_A3C664D3A76ED395, ADD INDEX IDX_A3C664D3A76ED395 (user_id)');
        $this->addSql('DROP INDEX IDX_A3C664D3E899029B ON subscription');
        $this->addSql('ALTER TABLE subscription DROP plan_id, CHANGE status status VARCHAR(20) NOT NULL');
    }
}
