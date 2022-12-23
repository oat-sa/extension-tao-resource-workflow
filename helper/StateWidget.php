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
 */

namespace oat\taoResourceWorkflow\helper;

use common_Utils;
use core_kernel_classes_Resource;
use oat\tao\helpers\form\elements\xhtml\XhtmlRenderingTrait;
use oat\oatbox\service\ServiceManager;
use oat\generis\model\OntologyAwareTrait;
use oat\taoResourceWorkflow\model\ResourceWorkflowService;
use oat\taoResourceWorkflow\model\wfmodel\JsonWorkflow;
use oat\taoResourceWorkflow\model\wfmodel\StateObject;
use oat\taoResourceWorkflow\model\WorkflowModel;
use tao_helpers_form_FormElement;

/**
 * Widget to represent the current state
 */
class StateWidget extends tao_helpers_form_FormElement
{
    use XhtmlRenderingTrait;
    use OntologyAwareTrait;

    public const WIDGET_ID = 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#StateWidget';

    /**
     * Render the Widget of the state and transition
     *
     * @return string
     * @throws \core_kernel_persistence_Exception
     * @throws \oat\oatbox\service\exception\InvalidServiceManagerException
     */
    public function render()
    {
        $val = $this->getValue();
        $stateResource = null;
        if ($val instanceof core_kernel_classes_Resource) {
            $stateResource = $this->getValue();
        } elseif ($val !== null && common_Utils::isUri($val)) {
            $stateResource = $this->getResource($val);
        }
        $returnValue = '';

        if (!is_null($stateResource)) {
            $state = $this->getResourceWorkflowService()->getStateByStateResource($stateResource)
                ?? $this->getInitialStateForResource();
            $returnValue = $this->renderLabel();
            $returnValue .= "<span id='" . $this->getName() . "' ";
            $returnValue .= $this->renderAttributes();
            $returnValue .= '>' . _dh($state->getLabel()) . '</span><br />';
            foreach ($state->getTransitions() as $transition) {
                $id = $this->getName() . '_' . $transition->getId();

                $returnValue .= "<a id='"
                    . $id
                    . "' class='btn-button small' data-href='"
                    . $transition->getUrl()
                    . "'";

                $returnValue .= '>' . _dh($transition->getLabel()) . '</a>';
                $returnValue .= "<script type=\"text/javascript\">
                    require(['jquery'], function($){
                        $(\"#" . $id . "\").on('click',function(event) {
                        event.preventDefault();
                        $.ajax({
                            url: '" . $transition->getUrl() . "',
                            type: \"POST\",
                            data: {resource: $(\"input[name='id']\").val()},
                            dataType: 'json',
                            success: function(response){
                                if(response.success) {
                                  $('.tree').trigger('refresh.taotree');
                                } else {
                                  // error handling
                                }
                            }
                        });
                     });
                });</script>";
            }
        }
        return (string)$returnValue;
    }

    /**
     * Added to prevent warnings, value is not realy used to store state
     *
     * @see tao_helpers_form_FormElement::getValue()
     */
    public function getValue()
    {
        return $this->getRawValue();
    }

    private function getResourceWorkflowService(): ResourceWorkflowService
    {
        return ServiceManager::getServiceManager()->get(ResourceWorkflowService::SERVICE_ID);
    }

    /**
     * @throws ElementDoesNotDefineResourceTypeException
     * @throws NoInitialStateForResourceException
     */
    private function getInitialStateForResource(): StateObject
    {
        $initialStateMap = $this->getWorkflowModel()
            ->getOption(JsonWorkflow::OPTION_INITIAL_STATE);

        $attr = $this->getAttributes();
        if (!isset($attr['resourceType'])) {
            throw new ElementDoesNotDefineResourceTypeException(
                'Element does not define resourceType'
            );
        }

        if (!isset($initialStateMap[$attr['resourceType']])) {
            throw new NoInitialStateForResourceException(
                sprintf('Resource %s does not have initial workflow state defined', $attr['resourceType'])
            );
        }
        return $this->getWorkflowModel()->getState($initialStateMap[$attr['resourceType']]);
    }

    private function getWorkflowModel(): WorkflowModel
    {
        return ServiceManager::getServiceManager()->get(WorkflowModel::SERVICE_ID);
    }
}
