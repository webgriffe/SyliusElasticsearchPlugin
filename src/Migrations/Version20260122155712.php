<?php

declare(strict_types=1);

namespace Webgriffe\SyliusElasticsearchPlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260122155712 extends AbstractMigration
{
    #[\Override]
    public function getDescription(): string
    {
        return 'Add filterable field to product attributes and options if does not exist';
    }

    #[\Override]
    public function up(Schema $schema): void
    {
        $sm = $this->connection->createSchemaManager();

        $productAttributeTable = $sm->introspectTable('sylius_product_attribute');
        if (!$productAttributeTable->hasColumn('filterable')) {
            $this->addSql('ALTER TABLE sylius_product_attribute ADD filterable TINYINT(1) DEFAULT 0 NOT NULL');
        }

        $productOptionTable = $sm->introspectTable('sylius_product_option');
        if (!$productOptionTable->hasColumn('filterable')) {
            $this->addSql('ALTER TABLE sylius_product_option ADD filterable TINYINT(1) DEFAULT 0 NOT NULL');
        }
    }

    #[\Override]
    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE sylius_product_attribute DROP filterable');
        $this->addSql('ALTER TABLE sylius_product_option DROP filterable');
    }
}
