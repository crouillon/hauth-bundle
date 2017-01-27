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

namespace LpDigital\Bundle\HAuthBundle\Listener\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Response;



use LpDigital\Bundle\HAuthBundle\Entity\UserProfile;

/**
 * HybridAuth authentication event.
 *
 * @manufacturer Lp digital - http://www.lp-digital.fr
 * @copyright    ©2017 - Lp digital
 * @author       Charles Rouillon <charles.rouillon@lp-digital.fr>
 */
class HAuthEvent extends Event
{

    /**
     * The user's network profile.
     *
     * @var UserProfile
     */
    protected $userProfile;

    /**
     * The current response object.
     *
     * @var Response
     */
    protected $response;

    /**
     * The id of the target security context firewall.
     *
     * @var string
     */
    protected $firewallId;

    /**
     * Event constructor.
     *
     * @param UserProfile $profile
     * @param Response    $response
     * @param string      $firewallId
     */
    public function __construct(UserProfile $profile, Response $response, $firewallId)
    {
        $this->userProfile = $profile;
        $this->response = $response;
        $this->firewallId = $firewallId;
    }

    /**
     * Returns the user's network profile.
     *
     * @return UserProfile
     */
    public function getUserProfile()
    {
        return $this->userProfile;
    }

    /**
     * Returns the current response object.
     *
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Returns the id of the target security context firewall.
     * @return string
     */
    public function getFirewallId()
    {
        return $this->firewallId;
    }
}
