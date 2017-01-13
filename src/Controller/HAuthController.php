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

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use BackBee\Renderer\RendererInterface;

use LpDigital\Bundle\HAuthBundle\Entity\UserProfile;
use LpDigital\Bundle\HAuthBundle\HAuth;
use LpDigital\Bundle\HAuthBundle\Listener\Event\HAuthEvent;

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
     * The current instance of the renderer.
     *
     * @var RendererInterface
     */
    protected $renderer;

    /**
     * An event dispatcher.
     *
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * Controller constructor.
     *
     * @param Hauth $bundle
     */
    public function __construct(HAuth $bundle, EventDispatcherInterface $eventDispatcher = null)
    {
        $this->bundle = $bundle;
        $this->renderer = $bundle->getApplication()->getRenderer();
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Computes and returns the hook javascript file to interact with BackBee toolbar.
     *
     * @return Response
     */
    public function hookAction()
    {
        if (!$this->bundle->isRestFirewallEnabled()) {
            return new Response('Not Found', Response::HTTP_NOT_FOUND);
        }

        $providers = $this->bundle->getProviders(true);

        $socialSignin = $this->bundle->getActiveProvidersFromToken();
        foreach ($socialSignin as $activated) {
            if (isset($providers[$activated->getNetworkId()])) {
                $providers[$activated->getNetworkId()]['activated'] = true;
            }
        }

        $entryPoint = $this->bundle->getHAuthEntryPoint();
        $content = $this->renderer->partial('Hauth/hook.js.twig', ['entrypoint' => $entryPoint, 'providers' => $providers]);

        return new Response($content, Response::HTTP_OK, ['Content-Type' => 'text/javascript']);
    }

    /**
     * HybridAuth entry point.
     *
     * @param  Request $request
     *
     * @return Response
     *
     * @codeCoverageIgnore
     */
    public function hAuthAction(Request $request)
    {
        // Ensure session exists and is started, required by HybridAuth.
        $this->startSession($request);

        $provider = $request->get('p', '');
        $firewall = $request->get('f', '');

        if ($this->bundle->hasProvider($provider)) {
            try {
                $response = new Response('');
                $profile = $this->hybridAuth($provider);
                $event = new HAuthEvent($profile, $response, $firewall);
                $this->eventDispatcher->dispatch('hauth.auth.success', $event);

                return $event->getResponse();
            } catch (\Exception $ex) {
                $this->bundle->getApplication()->error('hauth-bundle: '.$ex->getMessage());

                return new Response('Something went wrong!', Response::HTTP_FORBIDDEN);
            }
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
        $hybridAuth = new \Hybrid_Auth($this->getHydridAuthConfig());
        $adapter = $hybridAuth->authenticate($provider);

        $userProfile = (array) $adapter->getUserProfile();
        $userProfile['network'] = $provider;

        return $this->bundle->storeUserProfile($userProfile);
    }

    /**
     * Computes Hybridauth configuration array.
     *
     * @return array
     */
    private function getHydridAuthConfig()
    {
        $authConfig = HAuth::getHybridAuthConfig($this->bundle->getConfig());
        if (isset($authConfig['base_url'])) {
            $authConfig['base_url'] = $this->bundle->getHAuthEntryPoint();
        }

        return $authConfig;
    }
}