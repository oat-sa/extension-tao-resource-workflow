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
 * 
 */
namespace oat\taoResourceWorkflow\model;

use oat\oatbox\service\ConfigurableService;
use oat\generis\model\OntologyAwareTrait;
use oat\generis\model\data\event\ResourceCreated;
/**
 * Service to manage the association of states
 * to resources and their transitions
 *
 * @access public
 * @author Joel Bout, <joel@taotesting.com>
 */
class ResourceWorkflowService extends ConfigurableService
{
    use OntologyAwareTrait;
    
    const SERVICE_ID = 'taoResourceWorkflow/workflow';
    
    const PROPERTY_STATE = 'http://www.tao.lu/Ontologies/TAO.rdf#WorkflowState';
    
    public function getStates($resourceIds)
    {
        $states = array();
        foreach ($resourceIds as $resourceId) {
            $resource = $this->getResource($resourceId);
            $stateId = $resource->getOnePropertyValue($this->getProperty(self::PROPERTY_STATE));
            $states[$resourceId] = is_null($stateId) ? null
                : $stateId instanceof \core_kernel_classes_Resource ? $stateId->getUri() : (string) $stateId;
        }
        return $states;
    }
    
    public function setState(\core_kernel_classes_Resource $resource, $stateId)
    {
        return $resource->editPropertyValues($this->getProperty(self::PROPERTY_STATE), $stateId);
    }

    public function executeTransition($resource, $transitionId)
    {
        $transition = $this->getWfModel()->getTransition($transitionId);
        $success = false;
        if ($transition->isAllowedOn($resource)) {
            $destinationId = $transition->getDestinationId();
            $success = $this->setState($resource, $destinationId);
        }
        return $success;
    }
    
    public function onCreate(ResourceCreated $event)
    {
        $resource = $event->getResource();
        $state = $this->getWfModel()->getInitialState($resource);
        if (!is_null($state)) {
            $resource->setPropertyValue($this->getProperty(ResourceWorkflowService::PROPERTY_STATE), $state->getId());
        }
    }

    protected function getWfModel()
    {
        return $this->getServiceManager()->get(WorkflowModel::SERVICE_ID);
    }
}
