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

use Symfony\Component\HttpFoundation\Response;

use BackBee\Bundle\AbstractAdminBundleController;

use LpDigital\Bundle\HAuthBundle\HAuth;

/**
 * hauth-bundle administration controller.
 *
 * @manufacturer Lp digital - http://www.lp-digital.fr
 * @copyright    ©2017 - Lp digital
 * @author       Charles Rouillon <charles.rouillon@lp-digital.fr>
 */
class AdminController extends AbstractAdminBundleController
{

    /**
     * Main administration form.
     *
     * @return string Index template rendering.
     */
    public function indexAction()
    {
        try {
            $this->isGranted('VIEW', $this->getBundle());

            return $this->render(
                'HAuth/Admin/Index.twig',
                [
                    'hauth' => $this->getBundle(),
                    'config' => HAuth::getHybridAuthConfig($this->getBundle()->getConfig()),
                    'firewalls' => array_keys($this->application->getConfig()->getSecurityConfig()['firewalls']),
                    'providers' => $this->getAvailableProviders()
                ]
            );
        } catch (\Exception $ex) {
            $this->notifyUser(self::NOTIFY_ERROR, $ex->getMessage());
        }
    }

    public function saveAction()
    {
        $this->granted('EDIT', $this->getBundle());

        $config = $this->getRequest()->request->all();
        $config['store_user_profile'] = isset($config['store_user_profile']) && 'true' === $config['store_user_profile'];
        $config['firewalls'] = $config['firewalls[]'];
        unset($config['firewalls[]']);

        return $this->indexAction();
        return $this->persistConfig($config)->indexAction();
    }

    /**
     * Renders provided template with parameters and returns the generated string.
     *
     * @param  string     $template   the template relative path
     * @param  array|null $parameters
     * @return string
     *
     * @codeCoverageIgnore
     */
    public function render($template, array $parameters = null, Response $response = null)
    {
        $params = array_merge([
                        'request' => $this->getRequest(),
                        'routing' => $this->routing,
                        'flash_bag' => $this->getFlashBag(),
                    ], $parameters ? : []);

        return $this->application->getRenderer()->partial($template, $params);
    }

    /**
     * Persists configuration.
     *
     * @param  array $config The array configuration to be persisted.
     *
     * @return AdminController
     */
    private function persistConfig(array $config)
    {
        try {
            $bundle = $this->getBundle();
            $bundle->getConfig()->setSection('hybridauth', $config, true);
            $bundleConfig = $bundle->getConfig()->getBundleConfig();
            $this->getContainer()->get('config.persistor')->persist(
                    $bundle->getConfig(), isset($bundleConfig['config_per_site']) ? $bundleConfig['config_per_site'] : false
            );
            $this->notifyUser(self::NOTIFY_SUCCESS, 'Configuration saved.');
        } catch (\Exception $e) {
            $this->notifyUser(self::NOTIFY_ERROR, 'Configuration not saved: ' . $e->getMessage());
        }
        return $this;
    }

    private function getAvailableProviders()
    {
        $providers = [];

        \Hybrid_Auth::initialize([]);
        if (false !== $files = scandir(\Hybrid_Auth::$config['path_providers'])) {
            foreach ($files as $file) {
                if (preg_match('/[a-z0-9]+\.php/i', $file)) {
                    $providers[] = substr($file, 0, -4);
                }
            }
        }

        return $providers;
    }
}
