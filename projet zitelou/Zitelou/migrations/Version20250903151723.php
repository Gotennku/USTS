<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250903151723 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE admin_log CHANGE admin_id admin_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE auth_token CHANGE user_id user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE authorized_app CHANGE child_id child_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE authorized_contact CHANGE child_id child_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE ban_list CHANGE user_id user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE child CHANGE parent_id parent_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE emergency_call CHANGE child_id child_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE emergency_contact CHANGE child_id child_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE feature_access CHANGE child_id child_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE geo_location CHANGE child_id child_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE parental_settings CHANGE user_id user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE password_reset_token CHANGE user_id user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE payment CHANGE subscription_id subscription_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE subscription CHANGE user_id user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE subscription_history CHANGE subscription_id subscription_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user_log CHANGE user_id user_id INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE admin_log CHANGE admin_id admin_id INT NOT NULL');
        $this->addSql('ALTER TABLE `child` CHANGE parent_id parent_id INT NOT NULL');
        $this->addSql('ALTER TABLE feature_access CHANGE child_id child_id INT NOT NULL');
        $this->addSql('ALTER TABLE auth_token CHANGE user_id user_id INT NOT NULL');
        $this->addSql('ALTER TABLE authorized_app CHANGE child_id child_id INT NOT NULL');
        $this->addSql('ALTER TABLE authorized_contact CHANGE child_id child_id INT NOT NULL');
        $this->addSql('ALTER TABLE ban_list CHANGE user_id user_id INT NOT NULL');
        $this->addSql('ALTER TABLE emergency_call CHANGE child_id child_id INT NOT NULL');
        $this->addSql('ALTER TABLE emergency_contact CHANGE child_id child_id INT NOT NULL');
        $this->addSql('ALTER TABLE geo_location CHANGE child_id child_id INT NOT NULL');
        $this->addSql('ALTER TABLE parental_settings CHANGE user_id user_id INT NOT NULL');
        $this->addSql('ALTER TABLE password_reset_token CHANGE user_id user_id INT NOT NULL');
        $this->addSql('ALTER TABLE subscription_history CHANGE subscription_id subscription_id INT NOT NULL');
        $this->addSql('ALTER TABLE user_log CHANGE user_id user_id INT NOT NULL');
        $this->addSql('ALTER TABLE payment CHANGE subscription_id subscription_id INT NOT NULL');
        $this->addSql('ALTER TABLE subscription CHANGE user_id user_id INT NOT NULL');
    }
}
