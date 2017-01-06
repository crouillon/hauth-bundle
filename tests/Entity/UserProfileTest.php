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

use LpDigital\Bundle\HAuthBundle\Entity\UserProfile;

/**
 * Tests suite for UserProfile class.
 *
 * @manufacturer Lp digital - http://www.lp-digital.fr
 * @copyright    ©2017 - Lp digital
 * @author       Charles Rouillon <charles.rouillon@lp-digital.fr>
 *
 * @covers LpDigital\Bundle\HAuthBundle\Entity\UserProfile
 */
class UserProfileTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @covers LpDigital\Bundle\HAuthBundle\Entity\UserProfile::__get()
     * @covers LpDigital\Bundle\HAuthBundle\Entity\UserProfile::__set()
     */
    public function testGetterSetter()
    {
        $profile = new UserProfile();

        $profile->network = 'network';
        $this->assertEquals('network', $profile->network);
    }

    /**
     * @covers            LpDigital\Bundle\HAuthBundle\Entity\UserProfile::__get()
     * @expectedException \InvalidArgumentException
     */
    public function testGetInvalidProperty()
    {
        $profile = new UserProfile();
        $profile->fake;
    }

    /**
     * @covers            LpDigital\Bundle\HAuthBundle\Entity\UserProfile::__set()
     * @expectedException \InvalidArgumentException
     */
    public function testSetInvalidProperty()
    {
        $profile = new UserProfile();
        $profile->fake = 'fake';
    }

    /**
     * @covers LpDigital\Bundle\HAuthBundle\Entity\UserProfile::hydrateProfile()
     */
    public function testHydrateProfile()
    {
        $data = [
            'network' => 'network',
            'fake' => 'fake',
        ];

        $profile = new UserProfile($data);

        $this->assertEquals('network', $profile->network);
    }
}
