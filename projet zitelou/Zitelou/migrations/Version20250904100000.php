<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250904100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add event_id column to stripe_webhook_log with unique index';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable('stripe_webhook_log');
        if (!$table->hasColumn('event_id')) {
            $this->addSql('ALTER TABLE stripe_webhook_log ADD event_id VARCHAR(150) DEFAULT NULL');
            $this->addSql('UPDATE stripe_webhook_log SET event_id = CONCAT(event_type, "::migrated") WHERE event_type IS NOT NULL');
            $this->addSql('CREATE UNIQUE INDEX UNIQ_WEBHOOK_EVENT_ID ON stripe_webhook_log (event_id)');
            $this->addSql('ALTER TABLE stripe_webhook_log ALTER event_id SET NOT NULL');
        }
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable('stripe_webhook_log');
        if ($table->hasColumn('event_id')) {
            $this->addSql('DROP INDEX UNIQ_WEBHOOK_EVENT_ID ON stripe_webhook_log');
            $this->addSql('ALTER TABLE stripe_webhook_log DROP event_id');
        }
    }
}
