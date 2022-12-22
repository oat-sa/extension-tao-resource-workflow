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
namespace oat\taoResourceWorkflow\model\wfmodel;

use oat\oatbox\user\User;
use oat\oatbox\PhpSerializable;
use oat\taoResourceWorkflow\model\WorkflowState;

class StateObject implements WorkflowState, PhpSerializable
{
    private $id;
    
    private $label;

    private $readRoles;
    
    private $writeRoles;
    
    private $transitions;
    
    public function __construct($id, $label, $readRoles = [], $writeRoles = [], $transitions = [])
    {
        $this->id = $id;
        $this->label = $label;
        $this->readRoles = $readRoles;
        $this->writeRoles = $writeRoles;
        $this->transitions = $transitions;
    }
    
    public function getId()
    {
        return $this->id;
    }
    
    public function getLabel()
    {
        return $this->label;
    }

    public function getTransitions()
    {
        return $this->transitions;
    }
    
    public function getAccessRights(User $user)
    {
        $rights = [];
        $roles = $user->getRoles();
        if (array_intersect($roles, $this->readRoles)) {
            $rights[] = 'READ';
        }
        if (array_intersect($roles, $this->writeRoles)) {
            $rights[] = 'WRITE';
        }
        return $rights;
    }

    public function getReadRoles(): array
    {
        return $this->readRoles;
    }

    public function getWriteRoles(): array
    {
        return $this->writeRoles;
    }

    public function __toPhpCode() {
        return 'new '.get_class($this).'('.
            \common_Utils::toPHPVariableString($this->id).','.
            \common_Utils::toPHPVariableString($this->label).','.
            \common_Utils::toPHPVariableString($this->readRoles).','.
            \common_Utils::toPHPVariableString($this->writeRoles).','.
            \common_Utils::toHumanReadablePhpString($this->transitions).
        ')';
    }
}
