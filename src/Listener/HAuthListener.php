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

use BackBee\Event\Event;
use BackBee\Renderer\AbstractRenderer;

use LpDigital\Bundle\HAuthBundle\Config\Configurator;
use LpDigital\Bundle\HAuthBundle\HAuth;

/**
 * Description of HAuthListener
 *
 * @manufacturer Lp digital - http://www.lp-digital.fr
 * @copyright    ©2017 - Lp digital
 * @author       Charles Rouillon <charles.rouillon@lp-digital.fr>
 */
class HAuthListener
{

    /**
     * The hauth bundle instance.
     *
     * @var HAuth
     */
    private $bundle;

    /**
     * Service listener constructor.
     *
     * @param Form $bundle The hauth bundle instance.
     */
    public function __construct(HAuth $bundle)
    {
        $this->bundle = $bundle;
    }

    /**
     * Occur on nestednode.page.render event.
     *
     * @param Event $event
     */
    public function onPageRender(Event $event)
    {
        $renderer = $event->getEventArgs();
        if (!$renderer instanceof AbstractRenderer) {
            return;
        }

        if (!$this->bundle->isRestFirewallEnabled()) {
            return;
        }

        $renderer->addFooterJs($this->bundle
                    ->getApplication()
                    ->getRouting()
                    ->getUrlByRouteName(Configurator::$bbHookRouteName, null, null, true, $this->bundle->getApplication()->getSite()));
    }
}
