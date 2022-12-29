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
 * Copyright (c) 2017-2022 (original work) Open Assessment Technologies SA
 *
 */

declare(strict_types=1);

namespace oat\taoResourceWorkflow\model\wfmodel;

use oat\oatbox\service\ConfigurableService;
use oat\generis\model\OntologyAwareTrait;
use oat\taoResourceWorkflow\model\WorkflowModel;
use oat\taoResourceWorkflow\model\WorkflowState;

/**
 * A simple workflow, generated based on a json file.
 *
 * An example of the format would be:
 * {
 *   "initial" : {
 *     "CLASS_URI" : "STATE_ID"
 *   },
 *   "states" : {
 *     "STATE_ID" : {
 *       "label" : "LABEL",
 *       "read" : ["ROLE1"],
 *       "write" : ["ROLE2"],
 *       "transitions" : [
 *         {
 *           "label" : "LABEL",
 *           "state" : "OTHER_STATE_ID"
 *         }
 *       ]
 *     }
 *   }
 * }
 *
 * @access public
 * @author Joel Bout, <joel@taotesting.com>
 */
class JsonWorkflow extends ConfigurableService implements WorkflowModel
{
    use OntologyAwareTrait;

    public const OPTION_STATES = 'states';
    public const OPTION_INITIAL_STATE = 'initial';
    public const OPTION_LANGUAGE = 'language';

    public const DEFAULT_IMPORT_LANG = 'en-US';

    /**
     * (non-PHPdoc)
     * @see \oat\taoResourceWorkflow\model\WorkflowModel::getState()
     */
    public function getState($stateId)
    {
        $states = $this->getOption(self::OPTION_STATES);

        return isset($states[$stateId]) ? $this->addAtomicRoles($states[$stateId]) : null;
    }

    /**
     * @inheritdoc
     */
    public function getStates()
    {
        return $this->hasOption(self::OPTION_STATES) ? $this->getOption(self::OPTION_STATES) : [];
    }

    public function getLanguage(): ?string
    {
        return $this->getOption(self::OPTION_LANGUAGE) ?? self::DEFAULT_IMPORT_LANG;
    }

    /**
     * (non-PHPdoc)
     * @see \oat\taoResourceWorkflow\model\WorkflowModel::getTransition()
     */
    public function getTransition($transitionId)
    {
        foreach ($this->getOption(self::OPTION_STATES) as $state) {
            foreach ($state->getTransitions() as $transition) {
                if ($transition->getId() == $transitionId) {
                    return $transition;
                }
            }
        }
    }

    /**
     * Initial state is determined by comparing the instance to a list of classes
     * and it if is an instance of one of them, it returns the associated state
     *
     * @see \oat\taoResourceWorkflow\model\WorkflowModel::getInitialState()
     */
    public function getInitialState(\core_kernel_classes_Resource $resource)
    {
        foreach ($this->getOption(self::OPTION_INITIAL_STATE) as $classUri => $stateId) {
            if ($resource->isInstanceOf($this->getClass($classUri))) {
                return $this->getState($stateId);
            }
        }
        return null;
    }

    /**
     * Build a workflow model from a json representation
     *
     * @param string $json
     * @return JsonWorkflow
     */
    public static function fromJson($json)
    {
        $jsonObject = json_decode($json);
        $initial = [];
        foreach ($jsonObject->initial as $class => $state) {
            $initial[$class] = $state;
        }
        $states = [];
        foreach ($jsonObject->states as $id => $stateModel) {
            $transitions = array();
            foreach ($stateModel->transitions as $transitionModel) {
                $tid = $id . '-' . count($transitions);
                $transitions[] = new TransitionObject($tid, $transitionModel->label, $transitionModel->state);
            }
            $state = new StateObject($id, $stateModel->label, $stateModel->read, $stateModel->write, $transitions);
            $states[$id] = $state;
        }

        $options = [
            self::OPTION_STATES => $states,
            self::OPTION_INITIAL_STATE => $initial,
        ];

        if (isset($jsonObject->language)) {
            $options[self::OPTION_LANGUAGE] = $jsonObject->language;
        }

        return new self($options);
    }

    private function addAtomicRoles(WorkflowState $state): WorkflowState
    {
        if (!$state instanceof StateObject) {
            return $state;
        }

        $stateReadRoles = $state->getReadRoles();
        $stateWriteRoles = $state->getWriteRoles();

        foreach ($this->getIncludedRoles() as $role => $atomicRoles) {
            if (in_array($role, $state->getReadRoles(), true)) {
                $stateReadRoles = array_merge($stateReadRoles, $atomicRoles);
            }

            if (in_array($role, $state->getWriteRoles(), true)) {
                $stateWriteRoles = array_merge($stateWriteRoles, $atomicRoles);
            }
        }

        return new StateObject(
            $state->getId(),
            $state->getLabel(),
            $stateReadRoles,
            $stateWriteRoles,
            $state->getTransitions()
        );
    }

    private function getIncludedRoles(): array
    {
        return $this->getOption(
            self::OPTION_INCLUDED_ROLES,
            require __DIR__ . '/../../config/mapping/IncludedRoles.php'
        );
    }
}
