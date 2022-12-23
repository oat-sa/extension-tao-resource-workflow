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
 */

namespace oat\taoResourceWorkflow\model\wfmodel;

use oat\oatbox\PhpSerializable;
use oat\taoResourceWorkflow\model\WorkflowTransition;

class TransitionObject implements WorkflowTransition, PhpSerializable
{
    private $id;
    private $label;
    private $destination;

    public function __construct($id, $label, $destination)
    {
        $this->id = $id;
        $this->label = $label;
        $this->destination = $destination;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function getUrl()
    {
        return _url('execute', 'Transition', 'taoResourceWorkflow', ['transition' => $this->id]);
    }

    public function isAllowedOn(\core_kernel_classes_Resource $resource)
    {
        return true;
    }

    public function getDestinationId()
    {
        return $this->destination;
    }

    public function __toPhpCode()
    {
        return 'new ' . get_class($this) . '(' .
            \common_Utils::toPHPVariableString($this->id) . ',' .
            \common_Utils::toPHPVariableString($this->label) . ',' .
            \common_Utils::toPHPVariableString($this->destination) .
            ')';
    }
}
