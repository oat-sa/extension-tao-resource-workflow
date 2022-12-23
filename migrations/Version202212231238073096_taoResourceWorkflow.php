<?php

declare(strict_types=1);

namespace oat\taoResourceWorkflow\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use oat\taoResourceWorkflow\model\PermissionProvider;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version202212231238073096_taoResourceWorkflow extends AbstractMigration
{

    public function getDescription(): string
    {
        return 'set TaoItems as an extension to care about when calculating privileges on workflows';
    }

    public function up(Schema $schema): void
    {
        $service = $this->getServiceManager()->get(PermissionProvider::SERVICE_ID);
        $optionsExtensionWithRoles = $service->getOption(PermissionProvider::OPTION_EXTENSIONS_WITH_ROLES);

        // no need to check if it exists before, the option is new
        $optionsExtensionWithRoles[] = 'taoItems';

        $service->setOption(PermissionProvider::OPTION_EXTENSIONS_WITH_ROLES, $optionsExtensionWithRoles);
        $this->getServiceManager()->register(PermissionProvider::SERVICE_ID, $service);
    }

    public function down(Schema $schema): void
    {
        $service = $this->getServiceManager()->get(PermissionProvider::SERVICE_ID);
        $optionsExtensionWithRoles = [];

        $service->setOption(PermissionProvider::OPTION_EXTENSIONS_WITH_ROLES, $optionsExtensionWithRoles);
        $this->getServiceManager()->register(PermissionProvider::SERVICE_ID, $service);
    }
}
