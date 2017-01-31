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

namespace LpDigital\Bundle\HAuthBundle\Test;

use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;

use BackBee\Site\Site;

use LpDigital\Bundle\HAuthBundle\Config\Configurator;
use LpDigital\Bundle\HAuthBundle\Entity\SocialSignIn;
use LpDigital\Bundle\HAuthBundle\Entity\UserProfile;
use LpDigital\Bundle\HAuthBundle\HAuth;

/**
 * Test suite for HAuth class.
 *
 * @copyright    ©2017 - Lp digital
 * @author       Charles Rouillon <charles.rouillon@lp-digital.fr>
 *
 * @covers LpDigital\Bundle\HAuthBundle\HAuth
 */
class HAuthTest extends HAuthBundleCase
{
    /**
     * Fix up the fixtures.
     */
    public function setUp()
    {
        parent::setUp();

        $em = $this->bundle->getEntityManager();
        $metadata = [
            $em->getClassMetadata(Site::class),
            $em->getClassMetadata(SocialSignIn::class),
            $em->getClassMetadata(UserProfile::class),
        ];

        $schema = new SchemaTool($em);
        $schema->createSchema($metadata);

        $site = new Site('site_uid', ['label' => 'site label']);
        $site->setServerName('www.backbee.com');
        $em->persist($site);
        $em->flush($site);

        $this->application->getContainer()->set('site', $site);
    }

    /**
     * @covers LpDigital\Bundle\HAuthBundle\HAuth::getHybridAuthConfig()
     */
    public function testGetHybridAuthConfig()
    {
        $this->assertEquals($this->bundle->getConfig()->getHybridauthConfig(), HAuth::getHybridAuthConfig($this->bundle->getConfig()));

        $this->bundle->getConfig()->deleteSection('hybridauth');
        $this->assertEquals([], HAuth::getHybridAuthConfig($this->bundle->getConfig()));
    }

    /**
     * @covers LpDigital\Bundle\HAuthBundle\HAuth::getHAuthEntryPoint()
     */
    public function testGetHAuthEntryPoint()
    {
        $this->assertEquals('http://www.backbee.com/hauth.html', $this->bundle->getHAuthEntryPoint());

        Configurator::$entryPointRouteName = 'fake';
        $this->assertNull($this->bundle->getHAuthEntryPoint());
    }

    /**
     * @covers LpDigital\Bundle\HAuthBundle\HAuth::getProviders()
     */
    public function testGetProviders()
    {
        $this->assertEquals(['Google', 'Facebook', 'Twitter'], array_keys($this->bundle->getProviders()));
        $this->assertEquals(['Google', 'Twitter'], array_keys($this->bundle->getProviders(true)));
    }

    /**
     * @covers LpDigital\Bundle\HAuthBundle\HAuth::getActiveProvidersFromToken()
     */
    public function testGetActiveProvidersFromToken()
    {
        $this->assertEquals([], $this->bundle->getActiveProvidersFromToken());

        $this->createAuthenticatedUser();
        $this->assertEquals([], $this->bundle->getActiveProvidersFromToken());

        $socialSignIn = new SocialSignIn(
                $this->bundle->getApplication()->getSite(),
                UserSecurityIdentity::fromToken($this->application->getBBUserToken()),
                'Google',
                'identifier'
        );
        $this->bundle->getEntityManager()->persist($socialSignIn);
        $this->bundle->getEntityManager()->flush($socialSignIn);

        $this->assertEquals([$socialSignIn], $this->bundle->getActiveProvidersFromToken());
    }

    /**
     * @covers LpDigital\Bundle\HAuthBundle\HAuth::hasProvider()
     */
    public function testHasProvider()
    {
        $this->assertTrue($this->bundle->hasProvider('Google'));
        $this->assertFalse($this->bundle->hasProvider('Facebook'));
        $this->assertFalse($this->bundle->hasProvider(''));
    }

    /**
     * @covers LpDigital\Bundle\HAuthBundle\HAuth::isRestFirewallEnabled()
     */
    public function testIsRestFirewallEnabled()
    {
        $this->assertTrue($this->bundle->isRestFirewallEnabled());

        $this->bundle->getConfig()->setSection('hybridauth', ['base_url' => '/hauth.html', 'firewalls' => []], true);
        $this->assertFalse($this->bundle->isRestFirewallEnabled());
    }

    /**
     * @covers LpDigital\Bundle\HAuthBundle\HAuth::storeUserProfile()
     */
    public function testStoreUserProfile()
    {
        $profile = new UserProfile(['network' => 'network', 'identifier' => 'identifier']);

        $this->assertEquals($profile, $this->bundle->storeUserProfile(['network' => 'network', 'identifier' => 'identifier']));
        $this->assertNull($this->bundle->getEntityManager()->getRepository(UserProfile::class)->find(['network' => 'network', 'identifier' => 'identifier']));

        $this->bundle->getConfig()->setSection('hybridauth', ['store_user_profile' => true], true);
        $this->assertEquals($profile, $this->bundle->storeUserProfile(['network' => 'network', 'identifier' => 'identifier']));
        $this->assertEquals($profile, $this->bundle->getEntityManager()->getRepository(UserProfile::class)->find(['network' => 'network', 'identifier' => 'identifier']));
    }

    /**
     * @covers LpDigital\Bundle\HAuthBundle\HAuth::removeUserProfile()
     */
    public function testRemoveUserProfile()
    {
        $profile = new UserProfile(['network' => 'network', 'identifier' => 'identifier']);
        $this->assertEquals($profile, $this->bundle->removeUserProfile($profile));

        $this->bundle->getEntityManager()->persist($profile);
        $this->bundle->getEntityManager()->flush($profile);

        $this->assertEquals($profile, $this->bundle->removeUserProfile($profile));
        $this->assertNull($this->bundle->getEntityManager()->getRepository(UserProfile::class)->find(['network' => 'network', 'identifier' => 'identifier']));
    }
}
