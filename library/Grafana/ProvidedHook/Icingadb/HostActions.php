<?php

namespace Icinga\Module\Grafana\ProvidedHook\Icingadb;

use Icinga\Application\Config;
use Icinga\Authentication\Auth;
use Icinga\Module\Icingadb\Hook\HostActionsHook;
use Icinga\Module\Icingadb\Model\Host;
use ipl\Web\Url;
use ipl\Web\Widget\Link;

class HostActions extends HostActionsHook
{
    public function getActionsForObject(Host $host): array
    {
        if (! Auth::getInstance()->hasPermission('grafana/showall')) {
            return [];
        }

        $config = Config::module('grafana')->getSection('grafana');
        $timerange = $config->get('timerangeAll', '1w/w');

        return [
            new Link(
                t('Show all graphs'),
                Url::fromPath('grafana/icingadbshow', ['host' => $host->name, 'timerange' => $timerange]),
                ['target' => '_next', 'icon' => 'gauge']
            )
        ];
    }
}
