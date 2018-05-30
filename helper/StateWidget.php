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
namespace oat\taoResourceWorkflow\helper;

use oat\tao\helpers\form\elements\xhtml\XhtmlRenderingTrait;
use oat\oatbox\service\ServiceManager;
use oat\generis\model\OntologyAwareTrait;
use oat\taoResourceWorkflow\model\ResourceWorkflowService;

/**
 * Widget to represent the current state
 */
class StateWidget extends \tao_helpers_form_FormElement
{
    use XhtmlRenderingTrait;
    use OntologyAwareTrait;

    /**
     * A reference to the Widget Definition URI.
     *
     * @var string
     */
    protected $widget = 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#StateWidget';

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
        if ($val instanceof \core_kernel_classes_Resource) {
            $stateResource = $this->getValue();
        } else if ($val !== null && \common_Utils::isUri($val)) {
            $stateResource =  $this->getResource($val);
        }
        $returnValue = '';
        if (!is_null($stateResource)) {
            /** @var ResourceWorkflowService $resourceWorkflowService */
            $resourceWorkflowService = ServiceManager::getServiceManager()->get(ResourceWorkflowService::SERVICE_ID);
            $state = $resourceWorkflowService->getStateByStateResource($stateResource);
            $returnValue = $this->renderLabel();
            $returnValue .= "<span id='".$this->getName()."' ";
            $returnValue .= $this->renderAttributes();
            $returnValue .= '>' . _dh($state->getLabel()) . '</span><br />';
            foreach ($state->getTransitions() as $transition) {
                $id = $this->getName().'_'.$transition->getId();
                $returnValue .= "<a id='".$id."' class='btn-button small' data-href='".$transition->getUrl()."'";
                $returnValue .= '>' . _dh($transition->getLabel()) . '</a>';
                $returnValue .= "<script type=\"text/javascript\">
                    require(['jquery','jqueryui'], function($){
                        $(\"#".$id."\").on('click',function(event) {
                        event.preventDefault();
                        $.ajax({
                            url: '".$transition->getUrl()."',
                            type: \"POST\",
                            data: {resource: $(\"input[name='id']\").val()},
                            dataType: 'json',
                            success: function(response){
                                if(response.success) {
                                  $('.tree').trigger('refresh.taotree');
                                } else {
                                  // error handling
                                };
                            }
                        });
                     });
                });</script>";
            }
        }
        return (string) $returnValue;
    }

    /**
     * Added to prevent warnings, value is not realy used to store state
     * @see tao_helpers_form_FormElement::getValue()
     */
    public function getValue()
    {
        return $this->getRawValue();
    }
}
