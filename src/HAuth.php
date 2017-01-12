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

use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

use BackBee\Bundle\AbstractBundle;
use BackBee\Config\Config;
use BackBee\Utils\Collection\Collection;

use LpDigital\Bundle\HAuthBundle\Config\Configurator;
use LpDigital\Bundle\HAuthBundle\Entity\UserProfile;

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
     * Returns the URL to the entry point to HydridHauth.
     *
     * @return string|null
     */
    public function getHAuthEntryPoint()
    {
        $routing = $this->getApplication()->getRouting();
        if (null === $routing->get(Configurator::$entryPointRouteName)) {
            return null;
        }

        return $this->getApplication()
                        ->getRouting()
                        ->getUrlByRouteName(
                                Configurator::$entryPointRouteName,
                                null,
                                null,
                                true,
                                $this->getApplication()->getSite()
        );
    }

    /**
     * Return an array of valid providers.
     *
     * @param  boolean  $enabledOnly If true, filter enabled providers (default: false).
     *
     * @return string[]
     */
    public function getProviders($enabledOnly = false)
    {
        $providers = Collection::get(self::getHybridAuthConfig($this->getConfig()), 'providers', []);

        if (true === $enabledOnly) {
            $providers = array_filter($providers, function($var) {
                return isset($var['enabled']) && true === $var['enabled'];
            });
        }

        return $providers;
    }

    /**
     * Returns an array of existing social signins for token.
     *
     * @param  TokenInterface|null $token The token to look for social signins
     *                                    (default: current token).
     *
     * @return SocialSignIn[]
     */
    public function getActiveProvidersFromToken(TokenInterface $token = null)
    {
        if (null === $token) {
            $token = $this->getApplication()->getSecurityContext()->getToken();
        }

        if (null === $token) {
            return [];
        }

        $identity = UserSecurityIdentity::fromToken($token);

        return $this->getEntityManager()
                        ->getRepository(Entity\SocialSignIn::class)
                        ->findBy(['site' => $this->getApplication()->getSite(), 'strIdentity' => $identity]);
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
        $config = self::getHybridAuthConfig($this->getConfig());

        return true === Collection::get($config, 'providers:' . $provider . ':enabled');
    }

    /**
     * Is HybridAuth enabled for BackBee Rest Api?
     *
     * @return boolean
     */
    public function isRestFirewallEnabled()
    {
        $firewalls = Collection::get(self::getHybridAuthConfig($this->getConfig()), 'firewalls');

        return in_array(Configurator::$apiFirewallId, $firewalls);
    }

    /**
     * Gets a UserProfile from the received user profile and stores it if need.
     *
     * @param  array $profile
     *
     * @return UserProfile
     */
    public function storeUserProfile(array $profile)
    {
        $userProfile = new UserProfile($profile);

        if (true === Collection::get(self::getHybridAuthConfig($this->getConfig()), 'store_user_profile')) {
            $userProfile = $this->getEntityManager()
                    ->getRepository(UserProfile::class)
                    ->find(['network' => $profile['network'], 'identifier' => $profile['identifier']]);

            if (null === $userProfile) {
                $userProfile = new UserProfile();
            }

            $userProfile->hydrateProfile($profile);

            $this->getEntityManager()->persist($userProfile);
            $this->getEntityManager()->flush($userProfile);
        }

        return $userProfile;
    }

    /**
     * Removes the UserProfile from database.
     *
     * @param  UserProfile $userProfile
     *
     * @return UserProfile
     */
    public function removeUserProfile(UserProfile $userProfile)
    {
        $existing = $userProfile = $this->getEntityManager()
                    ->getRepository(UserProfile::class)
                    ->find(['network' => $userProfile->network, 'identifier' => $userProfile->identifier]);

        if (null !== $existing) {
            $this->getEntityManager()->remove($existing);
            $this->getEntityManager()->flush($existing);
        }

        return $userProfile;
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
