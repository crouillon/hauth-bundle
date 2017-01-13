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

namespace LpDigital\Bundle\HAuthBundle\Test\Listener\Event;

use Symfony\Component\HttpFoundation\Response;



use LpDigital\Bundle\HAuthBundle\Entity\UserProfile;
use LpDigital\Bundle\HAuthBundle\Listener\Event\HAuthEvent;

/**
 * Tests suite for HAuthEvent class.
 *
 * @manufacturer Lp digital - http://www.lp-digital.fr
 * @copyright    ©2017 - Lp digital
 * @author       Charles Rouillon <charles.rouillon@lp-digital.fr>
 *
 * @covers LpDigital\Bundle\HAuthBundle\Listener\Event\HAuthEvent
 */
class HAuthEventTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @covers LpDigital\Bundle\HAuthBundle\Listener\Event\HAuthEvent::__construct()
     * @covers LpDigital\Bundle\HAuthBundle\Listener\Event\HAuthEvent::getUserProfile()
     * @covers LpDigital\Bundle\HAuthBundle\Listener\Event\HAuthEvent::getResponse()
     * @covers LpDigital\Bundle\HAuthBundle\Listener\Event\HAuthEvent::getFirewallId()
     */
    public function testConstruct()
    {
        $profile = new UserProfile();
        $response = new Response();
        $firewall = 'fake';
        $event = new HAuthEvent($profile, $response, $firewall);

        $this->assertEquals($profile, $event->getUserProfile());
        $this->assertEquals($response, $event->getResponse());
        $this->assertEquals($firewall, $event->getFirewallId());
    }
}
