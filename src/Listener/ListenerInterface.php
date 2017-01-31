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

namespace LpDigital\Bundle\HAuthBundle\Listener;

use LpDigital\Bundle\HAuthBundle\HAuth;
use LpDigital\Bundle\HAuthBundle\Listener\Event\HAuthEvent;

/**
 * Interface for HAuth succeed authentication  listeners.
 *
 * @copyright    ©2017 - Lp digital
 * @author       Charles Rouillon <charles.rouillon@lp-digital.fr>
 */
interface ListenerInterface
{

    /**
     * Authentication listener constructor.
     *
     * @param HAuth  $bundle     The hauth bundle instance.
     * @param string $firewallId The id of the security context firewall.
     */
    public function __construct(HAuth $bundle, $firewallId);

    /**
     * Handles a succeed authentication throw HybridAuth.
     * Should try to authenticate a TokenInterface agains the firewall
     * then update the response object of $event.
     *
     * @param HAuthEvent $event
     */
    public function handle(HAuthEvent $event);

    /**
     * Is this listener can handle the event?
     *
     * @param  HAuthEvent $event
     *
     * @return boolean
     */
    public function supportEvent(HAuthEvent $event);
}
