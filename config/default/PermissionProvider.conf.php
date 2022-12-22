<?php

use oat\taoResourceWorkflow\model\PermissionProvider;

return new PermissionProvider(
    [
        PermissionProvider::OPTION_EXTENSIONS_WITH_ROLES => [
            'taoItems'
        ],
    ],
);
