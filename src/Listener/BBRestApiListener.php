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

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

use BackBee\Renderer\RendererInterface;
use BackBee\Security\SecurityContext;
use BackBee\Security\Token\BBUserToken;
use BackBee\Utils\Collection\Collection;

use LpDigital\Bundle\HAuthBundle\Entity\SocialSignIn;
use LpDigital\Bundle\HAuthBundle\HAuth;
use LpDigital\Bundle\HAuthBundle\Listener\Event\HAuthEvent;

/**
 * Handles HAuth succeed authentication for BackBee Rest API.
 *
 * @manufacturer Lp digital - http://www.lp-digital.fr
 * @copyright    ©2017 - Lp digital
 * @author       Charles Rouillon <charles.rouillon@lp-digital.fr>
 */
class BBRestApiListener implements ListenerInterface
{

    /**
     * The hauth bundle instance.
     *
     * @var HAuth
     */
    private $bundle;

    /**
     * The id of the security context firewall.
     *
     * @var string
     */
    private $firewallId;

    /**
     * The BackBee user provider.
     *
     * @var UserProviderInterface
     */
    private $userProvider;

    /**
     * The BackBee security context.
     *
     * @var SecurityContext
     */
    private $securityContext;

    /**
     * The BackBee renderer.
     *
     * @var RendererInterface
     */
    private $renderer;

    /**
     * Authentication listener constructor.
     *
     * @param HAuth  $bundle     The hauth bundle instance.
     * @param string $firewallId The id of the target security context firewall.
     */
    public function __construct(HAuth $bundle, $firewallId)
    {
        $this->bundle = $bundle;
        $this->firewallId = $firewallId;
        $this->securityContext = $bundle->getApplication()->getSecurityContext();
        $this->renderer = $bundle->getApplication()->getRenderer();
    }

    /**
     * Handles a succeed authentication throw HybridAuth.
     *
     * @param HAuthEvent $event
     */
    public function handle(HAuthEvent $event)
    {
        if (!$this->supportEvent($event)) {
            return;
        }

        $params = [
            'hasToken' => ($this->securityContext->getToken() instanceof BBUserToken),
            'status' => Response::HTTP_FORBIDDEN,
            'message' => 'Invalid authentication informations',
            'profile' => null,
            'network' => $event->getUserProfile()->network
        ];

        if ($params['hasToken']) {
            if (null === $socialSignin = $this->getSocialSignin($event)) {
                $socialSignin = new SocialSignIn(
                        $this->bundle->getApplication()->getSite(),
                        UserSecurityIdentity::fromToken($this->securityContext->getToken()),
                        $event->getUserProfile()->network,
                        $event->getUserProfile()->identifier
                );

                $this->bundle->getEntityManager()->persist($socialSignin);

                $params['status'] = Response::HTTP_OK;
                $params['message'] = sprintf('Authentication throw %s enabled.', $event->getUserProfile()->network);
                $params['profile'] = $event->getUserProfile();
            } else {
                $this->bundle->removeUserProfile($event->getUserProfile());
                $this->bundle->getEntityManager()->remove($socialSignin);

                $params['status'] = Response::HTTP_OK;
                $params['message'] = sprintf('Authentication throw %s disabled.', $event->getUserProfile()->network);
            }

            $this->bundle->getEntityManager()->flush($socialSignin);
        } elseif (
                (null !== $socialSignIn = $this->getSocialSignin($event))
                && (null !== $user = $this->getBackBeeUser($socialSignIn))
                && (null !== $token = $this->getAuthenticatedToken($user))
        ) {
            $params['status'] = Response::HTTP_OK;
            $params['key'] = $token->getUser()->getApiKeyPublic();
            $params['signature'] = $token->getNonce();
        }

        $content = $this->renderer->partial(sprintf('HAuth/%s.html.twig', $this->firewallId), $params);

        $response = $event->getResponse();
        $response->setStatusCode($params['status'])->setContent($content);
    }

    /**
     * Is this listener can handle the event?
     *
     * @param  HAuthEvent $event
     *
     * @return boolean
     */
    public function supportEvent(HAuthEvent $event)
    {
        return $this->bundle->isRestFirewallEnabled() && $this->firewallId === $event->getFirewallId() && null !== $this->getUserProvider();
    }

    /**
     * Returns the user provider for the target firewall.
     *
     * @return UserProviderInterface|null
     */
    private function getUserProvider()
    {
        if (null === $this->userProvider) {
            $securityConfig = (array) $this->bundle->getApplication()->getConfig()->getSecurityConfig();
            $userProviderId = Collection::get($securityConfig, sprintf('firewalls:%s:provider', $this->firewallId));
            $providers = $this->securityContext->getUserProviders();
            if (isset($providers[$userProviderId])) {
                $this->userProvider = $providers[$userProviderId];
            }
        }

        return $this->userProvider;
    }

    /**
     * Looks for a social sign in association
     *
     * @param  HAuth             $event The HybridHauth autentication event.
     *
     * @return SocialSignIn|null        The SociaSignIn object if found, null otherwise.
     */
    private function getSocialSignin(HAuthEvent $event)
    {
        $profile = $event->getUserProfile();

        return $this->bundle
                        ->getEntityManager()
                        ->getRepository(SocialSignIn::class)
                        ->findOneBy(['site' => $this->bundle->getApplication()->getSite(), 'networkId' => $profile->network, 'networkUserId' => $profile->identifier]);
    }

    /**
     * Looks for a BackBee user associated to the social signin.
     *
     * @param  SocialSignIn       $socialSignIn The social signin entity.
     *
     * @return UserInterface|null               A User instance if found, null otherwise.
     */
    private function getBackBeeUser(SocialSignIn $socialSignIn)
    {
        $identity = $socialSignIn->getIdentity();

        try {
            return $this->userProvider->loadUserByUsername($identity->getUsername());
        } catch (UsernameNotFoundException $ex) {
            return null;
        } catch (DisabledException $ex) {
            return null;
        }
    }

    /**
     * Gets an authenticated token.
     *
     * @param  UserInterface    $user The user to be authenticated.
     *
     * @return BBUserToken|null       An authenticated token or null.
     */
    private function getAuthenticatedToken(UserInterface $user)
    {
        $now = date('Y-m-d H:i:s');

        $token = new BBUserToken();
        $token->setUser($user->getUsername());
        $token->setCreated($now);
        $token->setNonce(md5(uniqid('', true)));
        $token->setDigest(md5($token->getNonce() . $now . $user->getPassword()));

        $authenticatedToken = $this->securityContext
                ->getAuthenticationManager()
                ->authenticate($token);

        if (!$authenticatedToken->getUser()->getApiKeyEnabled()) {
            return null;
        }

        $this->securityContext->setToken($authenticatedToken);

        return $authenticatedToken;
    }
}
