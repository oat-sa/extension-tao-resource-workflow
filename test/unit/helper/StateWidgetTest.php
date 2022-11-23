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
 * Copyright (c) 2022 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\taoResourceWorkflow\test\unit\helper;

use core_kernel_classes_Resource;
use oat\oatbox\service\ServiceManager;
use oat\tao\model\service\ApplicationService;
use oat\taoResourceWorkflow\helper\StateWidget;
use oat\taoResourceWorkflow\model\ResourceWorkflowService;
use oat\taoResourceWorkflow\model\wfmodel\JsonWorkflow;
use oat\taoResourceWorkflow\model\wfmodel\StateObject;
use oat\taoResourceWorkflow\model\wfmodel\TransitionObject;
use oat\taoResourceWorkflow\model\WorkflowModel;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StateWidgetTest extends TestCase
{
    private const ITEM_URI = 'ItemName';
    private const RESOURCE_TYPE_KEY = 'resourceType';
    private const RDF_ITEM_URI = 'http://www.tao.lu/Ontologies/TAOItem.rdf#Item';
    private const STATE_LABEL_1 = 'label_1';
    private const STATE_LABEL_2 = 'label_2';
    private const TRANSITION_URL_1 = 'url_1';
    private const TRANSITION_URL_2 = 'url_2';
    private const TRANSITION_ID_1 = 'id_1';
    private const TRANSITION_ID_2 = 'id_2';

    /** @var JsonWorkflow|MockObject  */
    private $workflowModelMock;

    /** @var ResourceWorkflowService|MockObject */
    private $resourceWorkflowServiceMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->workflowModelMock = $this->createMock(JsonWorkflow::class);
        ServiceManager::getServiceManager()->overload(
            WorkflowModel::SERVICE_ID,
            $this->workflowModelMock
        );

        $this->workflowModelMock->method('getOption')->willReturn(
            [
                self::RDF_ITEM_URI => 'concept',
                'http://www.tao.lu/Ontologies/TAOItem.rdf#Test' => 'authoring'
            ]
        );

        $this->resourceWorkflowServiceMock = $this->createMock(ResourceWorkflowService::class);
        ServiceManager::getServiceManager()->overload(
            ResourceWorkflowService::SERVICE_ID,
            $this->resourceWorkflowServiceMock
        );

        $applicationServiceMock = $this->createMock(ApplicationService::class);
        ServiceManager::getServiceManager()->overload(
            ApplicationService::SERVICE_ID,
            $applicationServiceMock
        );
        $applicationServiceMock->method('getDefaultEncoding')->willReturn('UTF-8');

    }
    public function testRenderHappyPath(): void
    {
        $stateMock = $this->createMock(StateObject::class);
        $transitionMock = $this->createMock(TransitionObject::class);
        $this->resourceWorkflowServiceMock->method('getStateByStateResource')->willReturn($stateMock);
        $subject = new StateWidget(self::ITEM_URI);
        $subject->setAttribute(self::RESOURCE_TYPE_KEY, self::RDF_ITEM_URI);
        $subject->setValue(new core_kernel_classes_Resource('SomeStateUri'));
        $subject->setDescription('SomeDescription');
        $this->workflowModelMock->method('getState')->willReturn($stateMock);

        $stateMock->method('getTransitions')->willReturn([
            $transitionMock,
            $transitionMock
        ]);

        $transitionMock
            ->method('getId')
            ->willReturnOnConsecutiveCalls(self::TRANSITION_ID_1, self::TRANSITION_ID_2);

        $transitionMock
            ->method('getLabel')
            ->willReturnOnConsecutiveCalls(self::STATE_LABEL_1, self::STATE_LABEL_2);

        $transitionMock
            ->method('getUrl')
            ->willReturnOnConsecutiveCalls(
                self::TRANSITION_URL_1,
                self::TRANSITION_URL_1,
                self::TRANSITION_URL_2,
                self::TRANSITION_URL_2
            );

        $result = $subject->render();

        $this->assertStringContainsString("<a id='ItemName_id_1'", $result);
        $this->assertStringContainsString("data-href='url_1'>label_1</a>", $result);

        $this->assertStringContainsString(
            "<label class='form_desc' for='ItemName'>SomeDescription</label",
            $result
        );

        $this->assertStringContainsString(
            "<a id='ItemName_id_2' class='btn-button small' data-href='url_2'>label_2</a>",
            $result
        );
    }
}
