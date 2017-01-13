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

namespace LpDigital\Bundle\HAuthBundle\Test\Entity;

use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;

use BackBee\Security\User;
use BackBee\Site\Site;

use LpDigital\Bundle\HAuthBundle\Entity\SocialSignIn;

/**
 * Tests suite for SocialSignIn class.
 *
 * @manufacturer Lp digital - http://www.lp-digital.fr
 * @copyright    ©2017 - Lp digital
 * @author       Charles Rouillon <charles.rouillon@lp-digital.fr>
 *
 * @covers Lpdigital\Bundle\HAuthBundle\Entity\SocialSignIn
 */
class SocialSignInTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @covers Lpdigital\Bundle\HAuthBundle\Entity\SocialSignIn::__construct()
     */
    public function testConstruct()
    {
        $site = new Site();
        $identity = new UserSecurityIdentity('fake', User::class);
        $networkId = 'social network';
        $networkUserId = 'user id';

        $socialsignin = new SocialSignIn($site, $identity, $networkId, $networkUserId);

        $this->assertEquals($site, $socialsignin->getSite());
        $this->assertEquals($identity, $socialsignin->getIdentity());
        $this->assertEquals($networkId, $socialsignin->getNetworkId());
        $this->assertEquals($networkUserId, $socialsignin->getNetworkUserId());
        $this->assertInstanceOf('\DateTime', $socialsignin->getCreated());
    }

    /**
     * @covers Lpdigital\Bundle\HAuthBundle\Entity\SocialSignIn::setIdentity()
     * @covers Lpdigital\Bundle\HAuthBundle\Entity\SocialSignIn::getIdentity()
     */
    public function testIdentity()
    {
        $expected = new UserSecurityIdentity('fake', User::class);
        $socialsignin = new SocialSignIn(new Site(), $expected, 'social network', 'user id');

        $reflection = new \ReflectionClass($socialsignin);
        $identity = $reflection->getProperty('identity');
        $identity->setAccessible(true);
        $strIdentity = $reflection->getProperty('strIdentity');
        $strIdentity->setAccessible(true);

        $this->assertEquals($expected->__toString(), $strIdentity->getValue($socialsignin));

        $identity->setValue($socialsignin, null);
        $this->assertEquals($expected, $socialsignin->getIdentity());
    }
}
