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

namespace LpDigital\Bundle\HAuthBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use LpDigital\Bundle\HAuthBundle\HAuth;
use LpDigital\Bundle\HAuthBundle\Entity\UserProfile;

/**
 * HybridAuth controller.
 *
 * @manufacturer Lp digital - http://www.lp-digital.fr
 * @copyright    ©2017 - Lp digital
 * @author       Charles Rouillon <charles.rouillon@lp-digital.fr>
 */
class HAuthController
{

    /**
     * The current instance of the bundle.
     *
     * @var HAuth
     */
    protected $bundle;

    /**
     * Controller constructor.
     *
     * @param Hauth $bundle
     */
    public function __construct(HAuth $bundle)
    {
        $this->bundle = $bundle;
    }

    /**
     * HybridAuth entry point.
     *
     * @param Request $request
     */
    public function hAuthAction(Request $request)
    {
        // Ensure session exists and is started, required by HybridAuth.
        $this->startSession($request);

        $provider = $request->get('provider', '');
        if ($this->bundle->hasProvider($provider)) {
            $userProfile = $this->hybridAuth($provider);

            var_dump($userProfile);
            return;
        }

        \Hybrid_Endpoint::process();
    }

    /**
     * Ensure session exists and is started, required by HybridAuth.
     *
     * @param Request $request
     */
    private function startSession(Request $request)
    {
        if (!$request->hasSession()) {
            $request->setSession($this->bundle->getApplication()->getSession());
        }

        $session = $request->getSession();
        if (!$session->isStarted()) {
            $session->start();
        }
    }

    /**
     * Tries to start a new session and initializes Hybrid_Auth.
     *
     * @param  string      $provider The signin provider.
     *
     * @return UserProfile           The user profile.
     *
     * @codeCoverageIgnore
     */
    private function hybridAuth($provider)
    {
        $hybridAuth = new \Hybrid_Auth($this->bundle->getHybridAuthConfig());
        $adapter = $hybridAuth->authenticate($provider);

        $userProfile = $adapter->getUserProfile();
        $userProfile['network'] = $provider;

        return new UserProfile($userProfile);
    }
}
