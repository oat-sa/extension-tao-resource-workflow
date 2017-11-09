<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA;
 *
 *
 */

namespace oat\taoResourceWorkflow\scripts\install;

use oat\generis\model\data\permission\implementation\FreeAccess;
use oat\generis\model\data\permission\implementation\IntersectionUnionSupported;
use oat\generis\model\data\permission\implementation\NoAccess;
use oat\oatbox\extension\InstallAction;
use oat\generis\model\data\event\ResourceCreated;
use oat\taoResourceWorkflow\model\ResourceWorkflowService;
use oat\taoResourceWorkflow\model\PermissionProvider;
use oat\generis\model\data\permission\PermissionInterface;

/**
 * Setup permision provider and resource creation event
 */
class SetupWorkflow extends InstallAction
{
    /**
     * @param $params
     */
    public function __invoke($params)
    {
        $this->registerEvent(ResourceCreated::class, [ResourceWorkflowService::SERVICE_ID, 'onCreate']);

        $impl = new PermissionProvider();
        $toRegister = $impl;

        $currentService = $this->getServiceManager()->get(PermissionProvider::SERVICE_ID);
        if(!$currentService instanceof FreeAccess && !$currentService instanceof NoAccess){
            if($currentService instanceof IntersectionUnionSupported){
                $toRegister = $currentService->add($impl);
            } else {
                $toRegister = new IntersectionUnionSupported(['inner' => [$currentService, $impl]]);
            }
        }

        $this->registerService(PermissionInterface::SERVICE_ID, $toRegister);

        return new \common_report_Report(\common_report_Report::TYPE_SUCCESS);
    }
}

