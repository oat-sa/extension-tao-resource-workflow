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

namespace oat\taoResourceWorkflow\scripts;

use oat\oatbox\extension\InstallAction;
use oat\generis\model\data\event\ResourceCreated;
use oat\taoResourceWorkflow\model\wfmodel\JsonWorkflow;
use oat\taoResourceWorkflow\model\WorkflowModel;
use oat\taoResourceWorkflow\model\ResourceWorkflowService;
use oat\taoResourceWorkflow\model\PermissionProvider;
use oat\generis\model\data\permission\PermissionManager;
use \common_report_Report as Report;

/**
 * Class RegisterEligibilityService
 * @package oat\taoProctoring\scripts\install
 */
class LoadWorkflowModel extends InstallAction
{
    /**
     * @param $params
     */
    public function __invoke($params)
    {
        if (count($params) < 1) {
            return new Report(Report::TYPE_ERROR, __('Usage: LoadWorkflowModel WORKFLOW_JSON'));
        };

        $file = array_shift($params);
        $json = file_get_contents($file);

        \json_decode($json);
        if (JSON_ERROR_NONE !== json_last_error()) {
            return new Report(Report::TYPE_ERROR, __('%s is not a valid JSON file', $file));
        }

        $this->registerService(WorkflowModel::SERVICE_ID, JsonWorkflow::fromJson($json));
        
        return new Report(Report::TYPE_SUCCESS, __('Successfully loaded model'));
    }
}

