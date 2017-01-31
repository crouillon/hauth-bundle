<?php

/*
 * Copyright (c) 2017 Lp digital system
 *
 * This file is part of hauth-bundle.
 *
 * hauth-bundle is free bundle: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * hauth-bundle is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with hauth-bundle. If not, see <http://www.gnu.org/licenses/>.
 */

namespace LpDigital\Bundle\HAuthBundle\Test\Config;

use org\bovigo\vfs\vfsStream;

use BackBee\Config\Config;
use BackBee\Tests\Mock\MockBBApplication;

use LpDigital\Bundle\HAuthBundle\Config\Configurator;

/**
 * Tests suite for Configurator class.
 *
 * @copyright    ©2017 - Lp digital
 * @author       Charles Rouillon <charles.rouillon@lp-digital.fr>
 *
 * @covers LpDigital\Bundle\HAuthBundle\Config\Configurator
 */
class ConfiguratorTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var MockBBApplication
     */
    protected $application;

    /**
     * Sets up the required fixtures.
     */
    public function setUp()
    {
        parent::setUp();

        $mockConfig = [
            'ClassContent' => [],
            'Config' => [
                'bootstrap.yml' => file_get_contents(__DIR__ . '/bootstrap.yml'),
                'config.yml' => file_get_contents(__DIR__ . '/config.yml'),
                'services.yml' => file_get_contents(__DIR__ . '/services.yml'),
            ],
            'cache' => [
                'container' => [],
                'twig' => []
            ],
            'log' => []
        ];

        vfsStream::umask(0000);
        vfsStream::setup('repositorydir', 0777, $mockConfig);

        $this->application = new MockBBApplication(null, null, false, $mockConfig, __DIR__ . '/../../vendor');
    }

    /**
     * @covers LpDigital\Bundle\HAuthBundle\Config\Configurator::loadRoutes()
     */
    public function testLoadRoutes()
    {
        $config = new Config(__DIR__);
        Configurator::loadRoutes($this->application, $config);

        $this->assertEquals('/hauth.html', $this->application->getRouting()->getRoutePath(Configurator::$entryPointRouteName));
        $this->assertEquals('/hauth-bundle/hook.js', $this->application->getRouting()->getRoutePath(Configurator::$bbHookRouteName));
    }

    /**
     * @covers LpDigital\Bundle\HAuthBundle\Config\Configurator::addEntryPointRoute()
     */
    public function testLoadEmptyRoutes()
    {
        $config = new Config(__DIR__);
        $config->setSection('hybridauth', ['base_url' => null]);
        Configurator::loadRoutes($this->application, $config);

        $this->assertNull($this->application->getRouting()->getRoutePath(Configurator::$entryPointRouteName));
        $this->assertNull($this->application->getRouting()->getRoutePath(Configurator::$bbHookRouteName));
    }

    /**
     * @covers LpDigital\Bundle\HAuthBundle\Config\Configurator::addHookRoute()
     */
    public function testLoadPartialRoutes()
    {
        $config = new Config(__DIR__);
        $config->setSection('hybridauth', ['base_url' => '/hauth.html', 'firewalls' => []], true);
        Configurator::loadRoutes($this->application, $config);

        $this->assertEquals('/hauth.html', $this->application->getRouting()->getRoutePath(Configurator::$entryPointRouteName));
        $this->assertNull($this->application->getRouting()->getRoutePath(Configurator::$bbHookRouteName));
    }

    /**
     * @covers LpDigital\Bundle\HAuthBundle\Config\Configurator::loadViews()
     */
    public function testLoadViews()
    {
        $config = new Config(__DIR__);
        Configurator::loadViews($this->application, $config);
        $renderer = $this->application->getContainer()->get('renderer')->dump();

        $this->assertTrue(in_array(realpath(__DIR__ . '/../../views'), $renderer['template_directories']));
    }
}
