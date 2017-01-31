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

namespace LpDigital\Bundle\HAuthBundle\Test\Controller;

use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;

use BackBee\Site\Site;

use LpDigital\Bundle\HAuthBundle\Controller\HAuthController;
use LpDigital\Bundle\HAuthBundle\Entity\SocialSignIn;
use LpDigital\Bundle\HAuthBundle\Entity\UserProfile;
use LpDigital\Bundle\HAuthBundle\Test\HAuthBundleCase;

/**
 * Tests suite for HAuthController class.
 *
 * @copyright    ©2017 - Lp digital
 * @author       Charles Rouillon <charles.rouillon@lp-digital.fr>
 *
 * @covers LpDigital\Bundle\HAuthBundle\Controller\HAuthController
 */
class HAuthControllerTest extends HAuthBundleCase
{
    /**
     * @var HAuthController
     */
    private $controller;

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
        $em->persist($site);
        $em->flush($site);

        $this->application->getContainer()->set('site', $site);
        $this->controller = $this->application->getContainer()->get('hauth.controller');
    }

    /**
     * @covers LpDigital\Bundle\HAuthBundle\Controller\HAuthController::hookAction()
     */
    public function testHookAction()
    {
        $this->createAuthenticatedUser();

        $SocialSignin = new SocialSignIn(
                $this->application->getSite(),
                UserSecurityIdentity::fromToken($this->application->getBBUserToken()),
                'Google', 'GoogleId');
        $this->bundle->getEntityManager()->persist($SocialSignin);
        $this->bundle->getEntityManager()->flush($SocialSignin);

        $response = $this->controller->hookAction();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals('text/javascript', $response->headers->get('Content-Type'));
        $this->assertContains('/hauth.html', $response->getContent());
        $this->assertContains('Google', $response->getContent());
        $this->assertNotContains('Facebook', $response->getContent());

        $this->bundle->getConfig()->setSection('hybridauth', ['base_url' => '/hauth.html', 'firewalls' => []], true);
        $responseNotFound = $this->controller->hookAction();

        $this->assertInstanceOf(Response::class, $responseNotFound);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $responseNotFound->getStatusCode());
    }

    /**
     * @covers LpDigital\Bundle\HAuthBundle\Controller\HAuthController::startSession()
     */
    public function testStartSession()
    {
        $request = new Request();
        self::invokeMethod($this->controller, 'startSession', [$request]);

        $this->assertTrue($request->getSession()->isStarted());
    }

    /**
     * @covers LpDigital\Bundle\HAuthBundle\Controller\HAuthController::getHydridAuthConfig()
     */
    public function testGetHydridAuthConfig()
    {
        $site = new Site();
        $site->setServerName('www.backbee.com');
        $this->application->getContainer()->set('site', $site);
        $authConfig = self::invokeMethod($this->controller, 'getHydridAuthConfig');

        $this->assertEquals('http://www.backbee.com/hauth.html', $authConfig['base_url']);
    }
}
