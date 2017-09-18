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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA
 */
namespace oat\taoResourceWorkflow\model;

use oat\generis\model\data\permission\PermissionInterface;
use oat\oatbox\user\User;
use oat\oatbox\service\ConfigurableService;
use oat\tao\model\TaoOntology;

/**
 * Simple permissible Permission model
 * 
 * does not require privileges
 * does not grant privileges
 *
 * @access public
 * @author Joel Bout, <joel@taotesting.com>
 */
class PermissionProvider extends ConfigurableService
    implements PermissionInterface
{
    /**
     * (non-PHPdoc)
     * @see \oat\generis\model\data\PermissionInterface::getPermissions()
     */
    public function getPermissions(User $user, array $resourceIds) {

        $roleIds = $user->getRoles();
        if (in_array(TaoOntology::PROPERTY_INSTANCE_ROLE_SYSADMIN, $roleIds)) {
            $permissions = array();
            foreach ($resourceIds as $id) {
                $permissions[$id] = $this->getSupportedRights();
            }
            return $permissions;
        }
        $wfservice = $this->getServiceManager()->get(ResourceWorkflowService::SERVICE_ID);
        $rights = array();
        $states = $wfservice->getStates($resourceIds);
        $stateCache = $this->getStateObjects(array_unique($states));
        foreach ($states as $id => $stateId) {
            if (!empty($stateId) && isset($stateCache[$stateId])) {
                $rights[$id] = $stateCache[$stateId]->getAccessRights($user);
            } else {
                $rights[$id] = $this->getSupportedRights();
            }
        }
        return $rights;
    }

    protected function getStateObjects($stateIds) {
        $wfmodel = $this->getServiceManager()->get(WorkflowModel::SERVICE_ID);
        $stateCache = array();
        foreach ($stateIds as $stateId) {
            if (!empty($stateId)) {
                $stateCache[$stateId] = $wfmodel->getState($stateId);
            }
        }
        return $stateCache;
    }

    /**
     * (non-PHPdoc)
     * @see \oat\generis\model\data\PermissionInterface::onResourceCreated()
     */
    public function onResourceCreated(\core_kernel_classes_Resource $resource) {
    }

    /**
     * (non-PHPdoc)
     * @see \oat\generis\model\data\permission\PermissionInterface::getSupportedRights()
     */
    public function getSupportedRights() {
        return array('WRITE', 'READ');
    }
}