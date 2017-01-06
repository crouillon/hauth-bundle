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

namespace LpDigital\Bundle\HAuthBundle;

use BackBee\Bundle\AbstractBundle;
use BackBee\Config\Config;
use BackBee\Utils\Collection\Collection;

/**
 * Description of HAuth
 *
 * @manufacturer Lp digital - http://www.lp-digital.fr
 * @copyright    ©2017 - Lp digital
 * @author       Charles Rouillon <charles.rouillon@lp-digital.fr>
 */
class HAuth extends AbstractBundle
{

    /**
     * Returns the HydridAuth configuration.
     *
     * @param  Config $config The bundle configuration object.
     *
     * @return array
     */
    public static function getHybridAuthConfig(Config $config)
    {
        $authConfig = $config->getHybridauthConfig();
        if (!is_array($authConfig)) {
            return [];
        }

        return $authConfig;
    }

    /**
     * Return an array of valid and enabled providers.
     *
     * @return string[]
     *
     * @codeCoverageIgnore
     */
    public function getProviders()
    {
        return Collection::get(self::getHybridAuthConfig($this->getConfig()), 'providers', []);
    }

    /**
     * Is the provider valid and enabled?
     *
     * @param  string  $provider A provider id.
     *
     * @return boolean           True if $provider exists and is enabled, False, otherwise.
     */
    public function hasProvider($provider)
    {
        return true === Collection::get(self::getHybridAuthConfig($this->getConfig()), 'providers:' . $provider . ':enabled');
    }

    /**
     * Method to call when we get the bundle for the first time.
     *
     * @codeCoverageIgnore
     */
    public function start()
    {

    }

    /**
     * Method to call before stop or destroy of current bundle.
     *
     * @codeCoverageIgnore
     */
    public function stop()
    {

    }
}
