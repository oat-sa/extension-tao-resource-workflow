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

    public const SERVICE_ID = 'taoResourceWorkflow/workflow';
    public const OPTION_EXTENSIONS_WITH_ROLES = 'extensions_with_roles';
    public const PROPERTY_STATE = 'http://www.tao.lu/Ontologies/TAO.rdf#WorkflowState';
    public const PROPERTY_STATE_ID = 'http://www.tao.lu/Ontologies/TAO.rdf#WorkflowStateId';
    public const CLASS_STATE = 'http://www.tao.lu/Ontologies/TAO.rdf#ResourceWorkflowStates';

    protected $stateUris = [];

    /**
     * @param  array $resourceIds
     * @return array
     * @throws \core_kernel_persistence_Exception
     * @throws \oat\oatbox\service\exception\InvalidServiceManagerException
     */
    public function getStates(array $resourceIds)
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

            $states[$resourceId] = $state instanceof WorkflowState
                ? $state->getId()
                : null
            ;
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
            $resource->setPropertyValue(
                $this->getProperty(ResourceWorkflowService::PROPERTY_STATE),
                $this->getStateUri($state)
            );
        }
    }

    public function updateOntology(): void
    {
        $states = $this->getWfModel()->getStates();
        $statesClass = $this->getClass(self::CLASS_STATE);
        $taoObjectClass = $this->getClass(TaoOntology::CLASS_URI_OBJECT);
        $resourceStateProp = $this->getProperty(self::PROPERTY_STATE);
        $language = $this->getWfModel()->getLanguage();

        foreach ($states as $state) {
            $stateResources = $statesClass->searchInstances(
                [
                    self::PROPERTY_STATE_ID => $state->getId()
                ],
                [
                    'like' => false,
                    'recursive' => true
                ]
            );

            if (empty($stateResources)) {
                $stateResource = $statesClass->createInstanceWithProperties(
                    [
                    self::PROPERTY_STATE_ID => $state->getId(),
                    ]
                );
            } else {
                $stateResource = reset($stateResources);
            }

            $labelProperty = $this->getProperty(OntologyRdfs::RDFS_LABEL);
            $stateResource->removePropertyValueByLg($labelProperty, $language);
            $stateResource->setPropertyValueByLg($labelProperty, $state->getLabel(), $language);

            $resourcesWithLegacyStateId = $taoObjectClass->searchInstances(
                [
                self::PROPERTY_STATE => $state->getId(),
                ],
                [
                    'like' => false,
                    'recursive' => true
                ]
            );
            foreach ($resourcesWithLegacyStateId as $resourceWithLegacyStateId) {
                $resourceWithLegacyStateId->editPropertyValues($resourceStateProp, $stateResource->getUri());
            }
        }
    }

    /**
     * @param  \core_kernel_classes_Resource $stateResource
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
     * @param  WorkflowState $state
     * @return string
     */
    protected function getStateUri(WorkflowState $state)
    {
        if (!isset($this->stateUris[$state->getId()])) {
            $stateResources = $this->getClass(self::CLASS_STATE)->searchInstances(
                [
                    self::PROPERTY_STATE_ID => $state->getId()
                ],
                [
                    'like' => false,
                    'recursive' => true
                ]
            );
            if (empty($stateResources)) {
                $this->stateUris[$state->getId()] = $state->getId();
            } else {
                $this->stateUris[$state->getId()] = reset($stateResources)->getUri();
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
