<?php

use Icinga\Authentication\Auth;
use Icinga\Module\Grafana\ProvidedHook\Icingadb\IcingadbSupport;

$auth = Auth::getInstance();

$this->providePermission('grafana/graphconfig', $this->translate('Allow to configure graphs.'));
$this->providePermission('grafana/graph', $this->translate('Allow to view graphs in dashboards.'));
$this->providePermission('grafana/debug', $this->translate('Allow to see debug information for graphs.'));
$this->providePermission('grafana/showall', $this->translate('Allow access to see all graphs of a host.'));
$this->providePermission('grafana/showlink', $this->translate('Display a link to the Grafana instance.'));

$this->provideConfigTab('config', [
    'title' => 'Configuration',
    'label' => 'Configuration',
    'url' => 'config'
]);

if ($auth->hasPermission('grafana/graphconfig')) {
    $section = $this->menuSection('Grafana Graphs')->setUrl('grafana/graph')->setPriority(999)->setIcon('chart-area');

    $section->add(N_('Graphs Configuration'))->setUrl('grafana/graph')->setPriority(30);
    $section->add(N_('Module Configuration'))->setUrl('grafana/config')->setPriority(40);

    $this->provideConfigTab('graph', [
        'title' => 'Graphs',
        'label' => 'Graphs',
        'url' => 'graph'
    ]);
}

$this->provideJsFile('behavior/iframe.js');
