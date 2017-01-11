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

namespace LpDigital\Bundle\HAuthBundle\Test;

use LpDigital\Bundle\HAuthBundle\HAuth;

/**
 * Test suite for HAuth
 *
 * @manufacturer Lp digital - http://www.lp-digital.fr
 * @copyright    ©2017 - Lp digital
 * @author       Charles Rouillon <charles.rouillon@lp-digital.fr>
 *
 * @covers LpDigital\Bundle\HAuthBundle\HAuth
 */
class HAuthTest extends HAuthBundleCase
{

    /**
     * @covers LpDigital\Bundle\HAuthBundle\HAuth::getHybridAuthConfig()
     */
    public function testGetHybridAuthConfig()
    {
        $this->assertEquals($this->bundle->getConfig()->getHybridauthConfig(), HAuth::getHybridAuthConfig($this->bundle->getConfig()));

        $this->bundle->getConfig()->deleteSection('hybridauth');
        $this->assertEquals([], HAuth::getHybridAuthConfig($this->bundle->getConfig()));
    }

    /**
     * @covers LpDigital\Bundle\HAuthBundle\HAuth::hasProvider()
     */
    public function testHasProvider()
    {
        $this->assertTrue($this->bundle->hasProvider('Google'));
        $this->assertFalse($this->bundle->hasProvider('Facebook'));
        $this->assertFalse($this->bundle->hasProvider(''));
    }
}
