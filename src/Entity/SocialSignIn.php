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
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;

use BackBee\Site\Site;

/**
 * A social network account association to a BackBee security identity.
 * Only one association per network is allowed for one BackBee identity.
 *
 * @copyright    ©2017 - Lp digital
 * @author       Charles Rouillon <charles.rouillon@lp-digital.fr>
 *
 * @ORM\Entity
 * @ORM\Table(name="bbx_hauth_socialsignin")
 */
class SocialSignIn
{

    /**
     * A BackBee site.
     *
     * @var Site
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="BackBee\Site\Site", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="site_uid", referencedColumnName="uid")
     */
    protected $site;

    /**
     * A symfony Security Identity.
     *
     * @var UserSecurityIdentity
     */
    protected $identity;

    /**
     * A symfony Security Identity string representation.
     *
     * @var string
     *
     * @ORM\Id
     * @ORM\Column(type="string", name="identity", nullable=false)
     */
    protected $strIdentity;

    /**
     * the network id.
     *
     * @var string
     *
     * @ORM\Id
     * @ORM\Column(type="string", name="network", nullable=false)
     */
    protected $networkId;

    /**
     * The user's unique id for network.
     *
     * @var string
     *
     * @ORM\Column(type="string", name="user_id", nullable=false)
     */
    protected $networkUserId;

    /**
     * The creation date.
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="created", nullable=false)
     */
    protected $created;

    /**
     * Class constructor.
     *
     * @param Site                 $site          A BackBee site.
     * @param UserSecurityIdentity $identity      A backbee security identity account.
     * @param string               $networkId     A network  identifier.
     * @param string               $networkUserId The user's unique id for network.
     */
    public function __construct(Site $site, UserSecurityIdentity $identity, $networkId, $networkUserId)
    {
        $this->site = $site;
        $this->setIdentity($identity);
        $this->networkId = $networkId;
        $this->networkUserId = $networkUserId;
        $this->created = new \DateTime();
    }

    /**
     * Sets the security identity account.
     *
     * @param UserSecurityIdentity $identity
     */
    protected function setIdentity(UserSecurityIdentity $identity)
    {
        $this->identity = $identity;
        $this->strIdentity = $this->identity->__toString();
    }

    /**
     * Returns the site.
     *
     * @return Site
     */
    public function getSite()
    {
        return $this->site;
    }

    /**
     * Returns the backbee security identity.
     *
     * @return UserSecurityIdentity
     */
    public function getIdentity()
    {
        $matches = [];
        if (null === $this->identity
            && is_string($this->strIdentity)
            && preg_match('/^UserSecurityIdentity\(([^,]+), (.+)\)$/', $this->strIdentity, $matches)
        ) {
            $this->identity = new UserSecurityIdentity($matches[1], $matches[2]);
        }

        return $this->identity;
    }

    /**
     * Returns the network identifier.
     *
     * @return string
     */
    public function getNetworkId()
    {
        return $this->networkId;
    }

    /**
     * Returns the user's unique id for network.
     *
     * @return string
     */
    public function getNetworkUserId()
    {
        return $this->networkUserId;
    }

    /**
     * Returns the creation date.
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }
}
