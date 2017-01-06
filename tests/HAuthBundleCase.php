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

use org\bovigo\vfs\vfsStream;
use Symfony\Component\Yaml\Yaml;

use BackBee\Security\Token\BBUserToken;
use BackBee\Security\User;
use BackBee\Tests\Mock\MockBBApplication;

use LpDigital\Bundle\HAuthBundle\HAuth;

/**
 * Test case for hauth-bundle.
 *
 * @manufacturer Lp digital - http://www.lp-digital.fr
 * @copyright    ©2017 - Lp digital
 * @author       Charles Rouillon <charles.rouillon@lp-digital.fr>
 */
class HAuthBundleCase extends \PHPUnit_Framework_TestCase
{

    /**
     * @var MockBBApplication
     */
    protected $application;

    /**
     * @var HAuth
     */
    protected $bundle;

    /**
     * Sets up the required fixtures.
     */
    public function setUp()
    {
        parent::setUp();

        $mockConfig = [
            'ClassContent' => [],
            'Config' => [
                'bootstrap.yml' => file_get_contents(__DIR__ . '/Config/bootstrap.yml'),
                'config.yml' => file_get_contents(__DIR__ . '/Config/config.yml'),
                'services.yml' => file_get_contents(__DIR__ . '/Config/services.yml'),
            ],
            'cache' => [
                'container' => [],
                'twig' => []
            ],
            'log' => []
        ];

        vfsStream::umask(0000);
        vfsStream::setup('repositorydir', 0777, $mockConfig);

        $this->application = new MockBBApplication(null, null, false, $mockConfig, __DIR__ . '/../vendor');
        $this->bundle = $this->application->getBundle('hauth');
        $this->bundle->getConfig()->setSection('hybridauth', Yaml::parse(file_get_contents(__DIR__ . '/Config/hybridauth.yml')));
    }

    /**
     * Creates a user for the specified group and authenticates a BBUserToken with the newly created user.
     * Note that the token is setted into application security context.
     */
    protected function createAuthenticatedUser(array $roles = ['ROLE_API_USER'])
    {
        $user = new User();
        $user
                ->setEmail('user@backbee.com')
                ->setLogin('user')
                ->setPassword('pass')
                ->setApiKeyPrivate(uniqid('PRIVATE', true))
                ->setApiKeyPublic(uniqid('PUBLIC', true))
                ->setApiKeyEnabled(true)
        ;

        $token = new BBUserToken($roles);
        $token->setAuthenticated(true);
        $token
                ->setUser($user)
                ->setCreated(new \DateTime())
                ->setLifetime(300)
        ;
        $this->bundle->getApplication()->getSecurityContext()->setToken($token);
    }

    /**
     * Call protected/private method of a class.
     *
     * @param  object &$object    Instantiated object that we will run method on.
     * @param  string $methodName Method name to call.
     * @param  array  $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     * @link https://jtreminio.com/2013/03/unit-testing-tutorial-part-3-testing-protected-private-methods-coverage-reports-and-crap/
     */
    public static function invokeMethod(&$object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    /**
     * Returns protected/private property value of a class.
     *
     * @param  object $object       Instantiated object that we will run method on.
     * @param  string $propertyName Property name to return.
     *
     * @return mixed                Property value return.
     * @link https://jtreminio.com/2013/03/unit-testing-tutorial-part-3-testing-protected-private-methods-coverage-reports-and-crap/
     */
    public static function invokeProperty($object, $propertyName)
    {
        $reflection = new \ReflectionClass(get_class($object));
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);

        return $property->getValue($object);
    }
}
