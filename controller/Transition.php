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

namespace oat\taoResourceWorkflow\controller;

use oat\taoResourceWorkflow\model\WorkflowModel;
use oat\generis\model\OntologyAwareTrait;
use oat\taoResourceWorkflow\model\ResourceWorkflowService;

/**
 * Sample controller
 *
 * @author  Open Assessment Technologies SA
 * @package taoResourceWorkflow
 * @license GPL-2.0
 */
class Transition extends \tao_actions_CommonModule
{
    use OntologyAwareTrait;

    /**
     * Execute a transition
     *
     * @requiresRight resource WRITE
     */
    public function execute()
    {
        $resource = $this->getResource($this->getRequestParameter('resource'));
        $transitionId = $this->getRequestParameter('transition');
        $workflowService = $this->getServiceManager()->get(ResourceWorkflowService::SERVICE_ID);
        $success = $workflowService->executeTransition($resource, $transitionId);

        return $this->returnJson(
            [
                'success' => $success
            ]
        );
    }
}
