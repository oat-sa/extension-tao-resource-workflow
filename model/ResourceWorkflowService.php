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
use oat\generis\model\OntologyRdfs;
use oat\tao\model\TaoOntology;

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
    const PROPERTY_STATE_ID = 'http://www.tao.lu/Ontologies/TAO.rdf#WorkflowStateId';
    const CLASS_STATE = 'http://www.tao.lu/Ontologies/TAO.rdf#ResourceWorkflowStates';

    protected $stateUris = [];

    public function getStates($resourceIds)
    {
        $states = array();
        foreach ($resourceIds as $resourceId) {
            $resource = $this->getResource($resourceId);
            $state = $resource->getOnePropertyValue($this->getProperty(self::PROPERTY_STATE));
            if ($state === null) {
                $states[$resourceId] = null;
                continue;
            }
            if ($state instanceof \core_kernel_classes_Resource) {
                $state = $this->getStateByStateResource($state);
            } else {
                $state = $this->getWfModel()->getState((string) $state);
            }
            $states[$resourceId] = $state->getId();
        }
        return $states;
    }
    
    public function setState(\core_kernel_classes_Resource $resource, $stateId)
    {
        $state = $this->getWfModel()->getState($stateId);
        return $resource->editPropertyValues($this->getProperty(self::PROPERTY_STATE), $this->getStateUri($state));
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
            $resource->setPropertyValue($this->getProperty(ResourceWorkflowService::PROPERTY_STATE), $this->getStateUri($state));
        }
    }

    public function updateOntology()
    {
        $states = $this->getWfModel()->getStates();
        $statesClass = $this->getClass(self::CLASS_STATE);
        $taoObjectClass = $this->getClass(TaoOntology::CLASS_URI_OBJECT);
        $resourceStateProp = $this->getProperty(self::PROPERTY_STATE);
        foreach ($states as $state) {
            $stateResources = $statesClass->searchInstances([
                self::PROPERTY_STATE_ID => $state->getId()
            ], ['like' => false, 'recursive' => true]);
            if (empty($stateResources)) {
                $stateResource = $statesClass->createInstanceWithProperties([
                    self::PROPERTY_STATE_ID => $state->getId(),
                    OntologyRdfs::RDFS_LABEL => $state->getLabel()
                ]);
            } else {
                $stateResource = current($stateResources);
                $stateResource->setLabel($state->getLabel());
            }
            $resourcesWithLegacyStateId = $taoObjectClass->searchInstances([
                self::PROPERTY_STATE => $state->getId(),
            ], ['like' => false, 'recursive' => true]);
            foreach ($resourcesWithLegacyStateId as $resourceWithLegacyStateId) {
                $resourceWithLegacyStateId->editPropertyValues($resourceStateProp, $stateResource->getUri());
            }
        }
    }

    /**
     * @param \core_kernel_classes_Resource $stateResource
     * @return WorkflowState
     * @throws \core_kernel_persistence_Exception
     * @throws \oat\oatbox\service\exception\InvalidServiceManagerException
     */
    public function getStateByStateResource(\core_kernel_classes_Resource $stateResource)
    {
        $stateId = $stateResource->getOnePropertyValue($this->getProperty(self::PROPERTY_STATE_ID));
        if ($stateId && $stateId instanceof \core_kernel_classes_Literal) {
            $stateId = (string) $stateId->literal;
        }
        return $this->getWfModel()->getState($stateId);
    }

    /**
     * @param WorkflowState $state
     * @return string
     */
    protected function getStateUri(WorkflowState $state)
    {
        if (!isset($this->stateUris[$state->getId()])) {
            $stateResources = $this->getClass(self::CLASS_STATE)->searchInstances([
                self::PROPERTY_STATE_ID => $state->getId()
            ], ['like' => false, 'recursive' => true]);
            if (empty($stateResources)) {
                $this->stateUris[$state->getId()] = $state->getId();
            } else {
                $this->stateUris[$state->getId()] = current($stateResources)->getUri();
            }
        }
        return $this->stateUris[$state->getId()];
    }

    /**
     * @throws \oat\oatbox\service\exception\InvalidServiceManagerException
     * @return WorkflowModel
     */
    protected function getWfModel()
    {
        return $this->getServiceManager()->get(WorkflowModel::SERVICE_ID);
    }
}
