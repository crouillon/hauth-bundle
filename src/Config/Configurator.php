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

namespace LpDigital\Bundle\HAuthBundle\Config;

use BackBee\ApplicationInterface;
use BackBee\Config\Config;
use BackBee\Routing\RouteCollection;
use BackBee\Utils\Collection\Collection;

use LpDigital\Bundle\HAuthBundle\HAuth;

/**
 * Configurator for hauth-bundle
 *
 * @copyright    ©2017 - Lp digital
 * @author       Charles Rouillon <charles.rouillon@lp-digital.fr>
 */
class Configurator
{

    /**
     * The identifier of the BackBee Rest API firewall.
     *
     * @var string
     */
    static public $apiFirewallId = 'rest_api_area';

    /**
     * The hybridauth entrypoint route name.
     *
     * @var string
     */
    static public $entryPointRouteName = 'hauth.entrypoint';

    /**
     * The toolbar hauth hook route name.
     *
     * @var string
     */
    static public $bbHookRouteName = 'hauth.bb.hook';

    /**
     * Adds routes of hybridauth library.
     *
     * @param ApplicationInterface $application
     * @param Config               $config
     */
    public static function loadRoutes(ApplicationInterface $application, Config $config)
    {
        if ($application->getContainer()->has('routing')) {
            $routing = $application->getContainer()->get('routing');

            self::addEntryPointRoute($routing, HAuth::getHybridAuthConfig($config));
        }
    }

    /**
     * Adds the bundle views folder to the script directories of the BackBee renderer.
     *
     * @param ApplicationInterface $application
     * @param Config               $config
     */
    public static function loadViews(ApplicationInterface $application, Config $config)
    {
        if ($application->getContainer()->has('renderer')) {
            $renderer = $application->getContainer()->get('renderer');
            $renderer->addScriptDir(realpath(__DIR__ . '/../../views'));
        }
    }

    /**
     * Adds the entry point route to HybridHaut if baseUrl is defined in config.
     *
     * @param RouteCollection $routing
     * @param array           $config
     */
    private static function addEntryPointRoute(RouteCollection $routing, array $config)
    {
        $baseURL = Collection::get($config, 'base_url');
        if (!empty($baseURL)) {
            $routing->pushRouteCollection([
                self::$entryPointRouteName => [
                    'pattern' => $baseURL,
                    'defaults' => [
                        '_action' => 'hAuthAction',
                        '_controller' => 'hauth.controller',
                    ]
                ]
            ]);

            self::addHookRoute($routing, $config);
        }
    }

    /**
     * Adds the toolbar hook if BackBee Rest API firewall is in config.
     *
     * @param RouteCollection $routing
     * @param array           $config
     */
    private static function addHookRoute(RouteCollection $routing, array $config)
    {
        $firewalls = Collection::get($config, 'firewalls', []);
        if (in_array(self::$apiFirewallId, $firewalls)) {
            $routing->pushRouteCollection([
                self::$bbHookRouteName => [
                    'pattern' => '/hauth-bundle/hook.js',
                    'defaults' => [
                        '_action' => 'hookAction',
                        '_controller' => 'hauth.controller',
                    ]
                ]
            ]);
        }
    }
}
