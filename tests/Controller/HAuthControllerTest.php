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

namespace LpDigital\Bundle\HAuthBundle\Test\Controller;

use Symfony\Component\HttpFoundation\Request;
use LpDigital\Bundle\HAuthBundle\Test\HAuthBundleCase;

/**
 * Tests suite for HAuthController class.
 *
 * @manufacturer Lp digital - http://www.lp-digital.fr
 * @copyright    ©2017 - Lp digital
 * @author       Charles Rouillon <charles.rouillon@lp-digital.fr>
 *
 * @covers LpDigital\Bundle\HAuthBundle\Controller\HAuthController
 */
class HAuthControllerTest extends HAuthBundleCase
{

    /**
     * @covers LpDigital\Bundle\HAuthBundle\Controller\HAuthController::startSession()
     */
    public function testStartSession()
    {
        $controller = $this->application->getContainer()->get('hauth.controller');

        $request = new Request();
        self::invokeMethod($controller, 'startSession', [$request]);

        $this->assertTrue($request->getSession()->isStarted());
    }
}
