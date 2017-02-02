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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 * 
 */
namespace oat\taoResourceWorkflow\model;

use oat\oatbox\user\User;
interface WorkflowState
{
    /**
     * Unique identifier of the state
     * @return string
     */
    public function getId();
    
    /**
     * Label to represent the state
     * @return string
     */
    public function getLabel();

    /**
     * Returns all transitions from this state
     *
     * @return WorkflowTransition[]
     */
    public function getTransitions();
    
    /**
     * Returns the rights the user has to resources that are
     * in the current state
     *
     * @param User $user
     * @return string[] right ids
     */
    public function getAccessRights(User $user);
}