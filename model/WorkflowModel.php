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
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

declare(strict_types=1);

namespace oat\taoResourceWorkflow\model;

interface WorkflowModel
{
    public const SERVICE_ID = 'taoResourceWorkflow/model';
    public const OPTION_INCLUDED_ROLES = 'includedRoles';

    /**
     * Returns the state with the given id
     *
     * @param string $stateId
     * @return WorkflowState
     */
    public function getState($stateId);

    /**
     * Get all the possible states
     *
     * @return WorkflowState[]
     */
    public function getStates();

    /**
     * Get language of services states
     */
    public function getLanguage(): ?string;

    /**
     * Returns the transition with the given id
     *
     * @param string $transitionId
     * @return WorkflowTransition
     */
    public function getTransition($transitionId);

    /**
     * Returns the initial state a newly created resource should be put in.
     * Will return NULL if none is applicable
     *
     * @param \core_kernel_classes_Resource $resource
     * @return WorkflowState
     */
    public function getInitialState(\core_kernel_classes_Resource $resource);
}
