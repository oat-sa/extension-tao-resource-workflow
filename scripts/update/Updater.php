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
 */

namespace oat\taoResourceWorkflow\scripts\update;

use oat\generis\model\data\permission\implementation\FreeAccess;
use oat\generis\model\data\permission\implementation\IntersectionUnionSupported;
use oat\generis\model\data\permission\implementation\NoAccess;
use oat\taoResourceWorkflow\model\PermissionProvider;
use oat\tao\scripts\update\OntologyUpdater;
use oat\taoResourceWorkflow\model\ResourceWorkflowService;

/**
 * @deprecated use migrations instead. See https://github.com/oat-sa/generis/wiki/Tao-Update-Process
 */
class Updater extends \common_ext_ExtensionUpdater
{
    /**
     * Resource Workflow updater
     */
    public function update($initialVersion)
    {

        $this->skip('0.1', '1.0.1');

        if ($this->isVersion('1.0.1')) {
            $currentService = $this->getServiceManager()->get(PermissionProvider::SERVICE_ID);
            if (
                !$currentService instanceof PermissionProvider
                && !$currentService instanceof FreeAccess
                && !$currentService instanceof NoAccess
            ) {
                if ($currentService instanceof IntersectionUnionSupported) {
                    $toRegister = $currentService->add(new PermissionProvider());
                } else {
                    $toRegister = new IntersectionUnionSupported(
                        ['inner' => [$currentService, new PermissionProvider()]]
                    );
                }
                $this->getServiceManager()->register(PermissionProvider::SERVICE_ID, $toRegister);
            }

            $this->setVersion('1.1.0');
        }

        $this->skip('1.1.0', '1.2.1');

        if ($this->isVersion('1.2.1')) {
            OntologyUpdater::syncModels();
            $this->getServiceManager()->get(ResourceWorkflowService::SERVICE_ID)->updateOntology();
            $this->setVersion('1.3.0');
        }

        $this->skip('1.3.0', '1.3.3');

        //Updater files are deprecated. Please use migrations.
        //See: https://github.com/oat-sa/generis/wiki/Tao-Update-Process

        $this->setVersion($this->getExtension()->getManifest()->getVersion());
    }
}
