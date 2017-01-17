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

namespace LpDigital\Bundle\HAuthBundle\Test\Listener;

use BackBee\Event\Event;
use BackBee\NestedNode\Page;

use LpDigital\Bundle\HAuthBundle\Listener\HAuthListener;
use LpDigital\Bundle\HAuthBundle\Test\HAuthBundleCase;

/**
 * Tests suite for HAuthListener class.
 *
 * @manufacturer Lp digital - http://www.lp-digital.fr
 * @copyright    ©2017 - Lp digital
 * @author       Charles Rouillon <charles.rouillon@lp-digital.fr>
 *
 * @covers LpDigital\Bundle\HAuthBundle\Listener\HAuthListener
 */
class HAuthListenerTest extends HAuthBundleCase
{

    /**
     * @var HAuthListener
     */
    protected $listener;

    /**
     * Sets up the required fixtures.
     */
    public function setUp()
    {
        parent::setUp();

        $this->listener = new HAuthListener($this->bundle);
    }

    /**
     * @covers LpDigital\Bundle\HAuthBundle\Listener\HAuthListener::onPageRender
     */
    public function testInvalidOnPageRender()
    {
        $bag = $this->invokeProperty($this->application->getRenderer(), 'externalResources');

        $this->listener->onPageRender(new Event(new Page()));
        $this->assertNull($bag->get('js_footer'));

        $this->bundle->getConfig()->setSection('hybridauth', ['firewalls' => []], true);
        $this->listener->onPageRender(new Event(new Page(), $this->application->getRenderer()));
        $this->assertNull($bag->get('js_footer'));

        $this->bundle->getConfig()->setSection('hybridauth', ['firewalls' => ['rest_api_area']], true);
        $this->listener->onPageRender(new Event(new Page(), $this->application->getRenderer()));
        $this->assertNull($bag->get('js_footer'));
    }

    /**
     * @covers LpDigital\Bundle\HAuthBundle\Listener\HAuthListener::onPageRender
     */
    public function testOnPageRender()
    {
        $this->application->getContainer()->set('bundle.toolbar', $this->bundle);
        $bag = $this->invokeProperty($this->application->getRenderer(), 'externalResources');

        $this->bundle->getConfig()->setSection('hybridauth', ['firewalls' => ['rest_api_area']], true);
        $this->listener->onPageRender(new Event(new Page(), $this->application->getRenderer()));
        $this->assertEquals(['/hauth-bundle/hook.js'], $bag->get('js_footer'));
    }
}
