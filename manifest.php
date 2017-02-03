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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA;
 */
use oat\tao\model\user\TaoRoles;
use oat\taoResourceWorkflow\controller\Transition;
use oat\taoResourceWorkflow\scripts\install\SetupWorkflow;

/**
 * Generated using taoDevTools 2.18.0
 */
return array(
    'name' => 'taoResourceWorkflow',
    'label' => 'Resource Workflow',
    'description' => 'Simple, resource/document based workflow allowing the transition between states',
    'license' => 'GPL-2.0',
    'version' => '0.1',
    'author' => 'Open Assessment Technologies SA',
    'requires' => array(
        'tao' => '>=7.56.0'
    ),
    'acl' => array(
        array('grant', TaoRoles::BACK_OFFICE, Transition::class),
    ),
    'install' => array(
        'rdf' => array(
            __DIR__.DIRECTORY_SEPARATOR.'scripts'.DIRECTORY_SEPARATOR.'ontology'.DIRECTORY_SEPARATOR.'workflow.rdf'
        ),
        'php' => array(
            SetupWorkflow::class
        )
    ),
    'uninstall' => array(
    ),
    'routes' => array(
        '/taoResourceWorkflow' => 'oat\\taoResourceWorkflow\\controller'
    ),
    'constants' => array(
        # views directory
        "DIR_VIEWS" => dirname(__FILE__).DIRECTORY_SEPARATOR."views".DIRECTORY_SEPARATOR,

        #BASE URL (usually the domain root)
        'BASE_URL' => ROOT_URL.'taoResourceWorkflow/',

        #BASE WWW required by JS
        'BASE_WWW' => ROOT_URL.'taoResourceWorkflow/views/'
    )
);