<?php

use oat\taoResourceWorkflow\model\PermissionProvider;
use oat\taoResourceWorkflow\model\ResourceWorkflowService;

return new PermissionProvider(
    [
        ResourceWorkflowService::OPTION_EXTENSIONS_WITH_ROLES => [],
    ],
);
