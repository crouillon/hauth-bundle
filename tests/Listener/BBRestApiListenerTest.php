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

namespace LpDigital\Bundle\HAuthBundle\Test\Listener;

use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;

use BackBee\Bundle\Registry;
use BackBee\Security\Repository\UserRepository;
use BackBee\Security\Token\BBUserToken;
use BackBee\Security\User;
use BackBee\Site\Site;

use LpDigital\Bundle\HAuthBundle\Entity\SocialSignIn;
use LpDigital\Bundle\HAuthBundle\Entity\UserProfile;
use LpDigital\Bundle\HAuthBundle\Listener\BBRestApiListener;
use LpDigital\Bundle\HAuthBundle\Listener\Event\HAuthEvent;
use LpDigital\Bundle\HAuthBundle\Test\HAuthBundleCase;

/**
 * Tests suite for BBRestApiListener class.
 *
 * @manufacturer Lp digital - http://www.lp-digital.fr
 * @copyright    ©2017 - Lp digital
 * @author       Charles Rouillon <charles.rouillon@lp-digital.fr>
 *
 * @covers LpDigital\Bundle\HAuthBundle\Listener\BBRestApiListener
 */
class BBRestApiListenerTest extends HAuthBundleCase
{

    /**
     * @var BBRestApiListener
     */
    protected $listener;

    /**
     * Sets up the required fixtures.
     */
    public function setUp()
    {
        parent::setUp();

        $em = $this->bundle->getEntityManager();
        $metadata = [
            $em->getClassMetadata(Site::class),
            $em->getClassMetadata(User::class),
            $em->getClassMetadata(SocialSignIn::class),
            $em->getClassMetadata(UserProfile::class),
            $em->getClassMetadata(Registry::class),
        ];

        $schema = new SchemaTool($em);
        $schema->createSchema($metadata);

        $site = new Site('site_uid', ['label' => 'site label']);
        $em->persist($site);
        $em->flush($site);

        $this->application->getContainer()->set('site', $site);
        $this->listener = new BBRestApiListener($this->bundle, 'rest_api_area');
    }

    /**
     * @covers LpDigital\Bundle\HAuthBundle\Listener\BBRestApiListener::handle()
     */
    public function testHandle()
    {
        // Unupported event
        $eventNok = new HAuthEvent(new UserProfile(), new Response(), 'fake');
        $this->listener->handle($eventNok);
        $this->assertTrue(empty($eventNok->getResponse()->getContent()));
        $this->assertEquals(Response::HTTP_OK, $eventNok->getResponse()->getStatusCode());

        // No user, no  token
        $eventOk = new HAuthEvent(new UserProfile(['network' => 'network', 'identifier' => 'identifier']), new Response(), 'rest_api_area');
        $this->listener->handle($eventOk);
        $this->assertContains('Invalid authentication informations', $eventOk->getResponse()->getContent());
        $this->assertEquals(Response::HTTP_FORBIDDEN, $eventOk->getResponse()->getStatusCode());

        $user = new User('user', md5('password'));
        $user->setActivated(true)
                ->setEmail('user@backbee.com')
                ->setApiKeyEnabled(true)
                ->setApiKeyPublic('public_key');
        $this->bundle->getEntityManager()->persist($user);

        $socialSignIn = new SocialSignIn(
        $this->bundle->getApplication()->getSite(),
            UserSecurityIdentity::fromAccount($user),
            'network',
            'identifier'
        );
        $this->bundle->getEntityManager()->persist($socialSignIn);
        $this->bundle->getEntityManager()->flush();

        // User authenticated, token created
        $this->listener->handle($eventOk);
        $this->assertContains('.authenticateBySign("public_key"', $eventOk->getResponse()->getContent());
        $this->assertEquals(Response::HTTP_OK, $eventOk->getResponse()->getStatusCode());

        // Token and SocialSignIn available => disable
        $this->listener->handle($eventOk);
        $this->assertContains('.success("Authentication throw network disabled.")', $eventOk->getResponse()->getContent());
        $this->assertEquals(Response::HTTP_OK, $eventOk->getResponse()->getStatusCode());
        $this->assertNull($this->invokeMethod($this->listener, 'getSocialSignin', [$eventOk]));

        // Token available but not SocialSignIn => enable
        $this->listener->handle($eventOk);
        $this->assertContains('.success("Authentication throw network enabled.")', $eventOk->getResponse()->getContent());
        $this->assertEquals(Response::HTTP_OK, $eventOk->getResponse()->getStatusCode());
        $this->assertNotNull($this->invokeMethod($this->listener, 'getSocialSignin', [$eventOk]));
    }

    /**
     * @covers LpDigital\Bundle\HAuthBundle\Listener\BBRestApiListener::supportEvent()
     */
    public function testSupportEvent()
    {
        $eventOk = new HAuthEvent(new UserProfile(), new Response(), 'rest_api_area');
        $this->assertTrue($this->listener->supportEvent($eventOk));

        $eventNok = new HAuthEvent(new UserProfile(), new Response(), 'fake');
        $this->assertFalse($this->listener->supportEvent($eventNok));

        $listenerWithoutProviders = new BBRestApiListener($this->bundle, 'fake');
        $this->assertFalse($listenerWithoutProviders->supportEvent($eventNok));

        $this->bundle->getConfig()->setSection('hybridauth', ['firewalls' => []], true);
        $this->assertFalse($this->listener->supportEvent($eventOk));
    }

    /**
     * @covers LpDigital\Bundle\HAuthBundle\Listener\BBRestApiListener::getUserProvider()
     */
    public function testGetUserProvider()
    {
        $listenerWithoutProviders = new BBRestApiListener($this->bundle, 'fake');
        $this->assertNull($this->invokeMethod($listenerWithoutProviders, 'getUserProvider'));

        $this->assertInstanceOf(UserRepository::class, $this->invokeMethod($this->listener, 'getUserProvider'));
    }

    /**
     * @covers LpDigital\Bundle\HAuthBundle\Listener\BBRestApiListener::getSocialSignin()
     */
    public function testGetSocialSignin()
    {
        $event = new HAuthEvent(new UserProfile(['network' => 'network', 'identifier' => 'identifier']), new Response(), 'rest_api_area');
        $this->assertNull($this->invokeMethod($this->listener, 'getSocialSignin', [$event]));

        $this->createAuthenticatedUser();
        $socialSignIn = new SocialSignIn(
                $this->bundle->getApplication()->getSite(),
                UserSecurityIdentity::fromToken($this->application->getBBUserToken()),
                'network',
                'identifier'
        );
        $this->bundle->getEntityManager()->persist($socialSignIn);
        $this->bundle->getEntityManager()->flush($socialSignIn);

        $this->assertEquals($socialSignIn, $this->invokeMethod($this->listener, 'getSocialSignin', [$event]));
    }

    /**
     * @covers LpDigital\Bundle\HAuthBundle\Listener\BBRestApiListener::getBackBeeUser()
     */
    public function testGetBackBeeUser()
    {
        $this->createAuthenticatedUser();
        $this->invokeMethod($this->listener, 'getUserProvider');

        $socialSignIn = new SocialSignIn(
                $this->bundle->getApplication()->getSite(),
                UserSecurityIdentity::fromToken($this->application->getBBUserToken()),
                'network',
                'identifier'
        );

        $this->assertNull($this->invokeMethod($this->listener, 'getBackBeeUser', [$socialSignIn]));

        $user = $this->application->getBBUserToken()->getUser();
        $this->bundle->getEntityManager()->persist($user);
        $this->bundle->getEntityManager()->flush($user);

        $this->assertNull($this->invokeMethod($this->listener, 'getBackBeeUser', [$socialSignIn]));

        $user->setActivated(true);
        $this->bundle->getEntityManager()->flush($user);

        $this->assertEquals($user, $this->invokeMethod($this->listener, 'getBackBeeUser', [$socialSignIn]));
    }

    /**
     * @covers LpDigital\Bundle\HAuthBundle\Listener\BBRestApiListener::getAuthenticatedToken()
     */
    public function testGetAuthenticatedToken()
    {
        $user = new User('user', md5('password'));
        $user->setActivated(true)->setEmail('user@backbee.com');

        $this->bundle->getEntityManager()->persist($user);
        $this->bundle->getEntityManager()->flush($user);

        $this->assertNull($this->invokeMethod($this->listener, 'getAuthenticatedToken', [$user]));

        $user->setApiKeyEnabled(true);
        $user->setApiKeyPublic('public_key');
        $this->bundle->getEntityManager()->flush($user);

        $token = $this->invokeMethod($this->listener, 'getAuthenticatedToken', [$user]);
        $this->assertInstanceOf(BBUserToken::class, $token);
        $this->assertTrue($token->isAuthenticated());
    }
}
