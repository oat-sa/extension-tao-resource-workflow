<?php

declare(strict_types=1);

namespace oat\taoResourceWorkflow\migrations;

use Doctrine\DBAL\Schema\Schema;
use oat\generis\model\data\permission\implementation\IntersectionUnionSupported;
use oat\tao\scripts\tools\migrations\AbstractMigration;
use oat\taoResourceWorkflow\model\PermissionProvider;
use oat\taoResourceWorkflow\model\ResourceWorkflowService;

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
        $service = $this->getServiceManager()->get(ResourceWorkflowService::SERVICE_ID);
        $optionsExtensionWithRoles = $service->getOption(ResourceWorkflowService::OPTION_EXTENSIONS_WITH_ROLES);
        $optionsExtensionWithRoles = array_merge($optionsExtensionWithRoles, ['taoItems']);

        $service->setOption(ResourceWorkflowService::OPTION_EXTENSIONS_WITH_ROLES, $optionsExtensionWithRoles);
        $this->getServiceManager()->register(ResourceWorkflowService::SERVICE_ID, $service);
    }

    public function down(Schema $schema): void
    {
        $service = $this->getServiceManager()->get(ResourceWorkflowService::SERVICE_ID);
        $optionsExtensionWithRoles = [];

        $service->setOption(ResourceWorkflowService::OPTION_EXTENSIONS_WITH_ROLES, $optionsExtensionWithRoles);
        $this->getServiceManager()->register(ResourceWorkflowService::SERVICE_ID, $service);
    }
}
