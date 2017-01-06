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

namespace LpDigital\Bundle\HAuthBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Stores the normalized user profile used by HybridAuth.
 *
 * @manufacturer Lp digital - http://www.lp-digital.fr
 * @copyright    ©2017 - Lp digital
 * @author       Charles Rouillon <charles.rouillon@lp-digital.fr>
 *
 * @ORM\Entity
 * @ORM\Table(name="bbx_userprofile")
 */
class UserProfile
{

    /**
     * the network id.
     *
     * @var string
     *
     * @ORM\Id
     * @ORM\Column(type="string", name="network", nullable=false)
     */
    protected $network;

    /**
     * The user's unique id for network.
     *
     * @var string
     *
     * @ORM\Id
     * @ORM\Column(type="string", name="identifier", nullable=false)
     */
    protected $identifier;

    /**
     * URL link to profile page on the IDp web site.
     *
     * @var string
     *
     * @ORM\Column(type="string", name="profileURL", nullable=true)
     */
    protected $profileURL;

    /**
     * User website, blog, web page.
     *
     * @var string
     *
     * @ORM\Column(type="string", name="webSiteURL", nullable=true)
     */
    protected $webSiteURL;

    /**
     * URL link to user photo or avatar.
     *
     * @var string
     *
     * @ORM\Column(type="string", name="photoURL", nullable=true)
     */
    protected $photoURL;

    /**
     * User dispalyName provided by the IDp or a concatenation of first and last name.
     *
     * @var string
     *
     * @ORM\Column(type="string", name="displayName", nullable=true)
     */
    protected $displayName;

    /**
     * A short about_me.
     *
     * @var string
     *
     * @ORM\Column(type="string", name="description", nullable=true)
     */
    protected $description;

    /**
     * User's first name.
     *
     * @var string
     *
     * @ORM\Column(type="string", name="firstName", nullable=true)
     */
    protected $firstName;

    /**
     * User's last name.
     *
     * @var string
     *
     * @ORM\Column(type="string", name="lastName", nullable=true)
     */
    protected $lastName;

    /**
     * User's gender. Values are 'female', 'male' or NULL.
     *
     * @var string
     *
     * @ORM\Column(type="string", name="gender", nullable=true)
     */
    protected $gender;

    /**
     * User's language.
     *
     * @var string
     *
     * @ORM\Column(type="string", name="language", nullable=true)
     */
    protected $language;

    /**
     * User's age, note that we dont calculate it. we return it as is if the IDp provide it.
     *
     * @var integer
     *
     * @ORM\Column(type="integer", name="age", nullable=true)
     */
    protected $age;

    /**
     * The day in the month in which the person was born.
     *
     * @var integer
     *
     * @ORM\Column(type="integer", name="birthDay", nullable=true)
     */
    protected $birthDay;

    /**
     * The month in which the person was born.
     *
     * @var integer
     *
     * @ORM\Column(type="integer", name="birthMonth", nullable=true)
     */
    protected $birthMonth;

    /**
     * The year in which the person was born.
     *
     * @var integer
     *
     * @ORM\Column(type="integer", name="birthYear", nullable=true)
     */
    protected $birthYear;

    /**
     * User email. Not all of IDp garant access to the user email.
     *
     * @var string
     *
     * @ORM\Column(type="string", name="email", nullable=true)
     */
    protected $email;

    /**
     * Verified user email. Note: not all of IDp garant access to verified user email.
     *
     * @var string
     *
     * @ORM\Column(type="string", name="emailVerified", nullable=true)
     */
    protected $emailVerified;

    /**
     * User's phone number.
     *
     * @var string
     *
     * @ORM\Column(type="string", name="phone", nullable=true)
     */
    protected $phone;

    /**
     * User's address.
     *
     * @var string
     *
     * @ORM\Column(type="string", name="address", nullable=true)
     */
    protected $address;

    /**
     * User's country.
     *
     * @var string
     *
     * @ORM\Column(type="string", name="country", nullable=true)
     */
    protected $country;

    /**
     * User's state or region.
     *
     * @var string
     *
     * @ORM\Column(type="string", name="region", nullable=true)
     */
    protected $region;

    /**
     * User's city.
     *
     * @var string
     *
     * @ORM\Column(type="string", name="city", nullable=true)
     */
    protected $city;

    /**
     * User's city.
     *
     * @var string
     *
     * @ORM\Column(type="string", name="zip", nullable=true)
     */
    protected $zip;

    /**
     * User's job title.
     *
     * @var string
     *
     * @ORM\Column(type="string", name="job_title", nullable=true)
     */
    protected $job_title;

    /**
     * User's organization.
     *
     * @var string
     *
     * @ORM\Column(type="string", name="organization_name", nullable=true)
     */
    protected $organization_name;

    /**
     * Class constructor.
     *
     * @param array $profile The user's data.
     */
    public function __construct(array $profile = [])
    {
        $this->hydrateProfile($profile);
    }

    /**
     * Magic property getter.
     *
     * @param  string              $name The property name.
     *
     * @return mixed                     The property value.
     *
     * @throws \InvalidArgumentException Thrown if the property $name does not exists.
     */
    public function __get($name)
    {
        if (!property_exists(self::class, $name)) {
            throw new \InvalidArgumentException(sprintf('Property %s does not exist.', $name));
        }

        return $this->$name;
    }

    /**
     * Magic property setter.
     *
     * @param  string             $name  The property name.
     * @param  mixed              $value The property value.
     *
     * @throws \InvalidArgumentException Thrown if the property $name does not exists.
     */
    public function __set($name, $value)
    {
        if (!property_exists(self::class, $name)) {
            throw new \InvalidArgumentException(sprintf('Property %s does not exist.', $name));
        }

        $this->$name = $value;
    }

    /**
     * Hydrates a user profle throw an array.
     *
     * @param array $profile The user's data.
     */
    public function hydrateProfile(array $profile = [])
    {
        foreach ($profile as $name => $value) {
            if (property_exists(self::class, $name)) {
                $this->$name = $value;
            }
        }
    }
}
