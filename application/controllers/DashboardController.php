<?php

namespace Icinga\Module\Grafana\Controllers;

use Icinga\Module\Grafana\ProvidedHook\Icingadb\IcingadbSupport;

use Icinga\Application\Modules\Module;
use Icinga\Module\Monitoring\Controller;
use Icinga\Module\Monitoring\Object\Host;
use Icinga\Module\Monitoring\Object\Service;

use ipl\Web\Url;

/**
 * DashboardController for showing graphs for Monitoring Module dashboards
 */
class DashboardController extends Controller
{
    public function init()
    {
        $this->assertPermission('grafana/graph');
        $this->setAutorefreshInterval(15);
    }

    public function indexAction()
    {
        // Redirect to IcingaDB if it is enabled
        if (Module::exists('icingadb') && IcingadbSupport::useIcingaDbAsBackend()) {
            $this->redirectNow(Url::fromPath('grafana/icingadbdashboard')->setQueryString($this->params));
        }

        $this->getTabs()->add('graphs', [
            'active' => true,
            'label' => $this->translate('Graphs'),
            'url' => $this->getRequest()->getUrl()
        ]);

        $hostname = $this->params->getRequired('host');
        $servicename = $this->params->get('service');

        if ($servicename != null) {
            $object = new Service($this->backend, $hostname, $servicename);
        } else {
            $object = new Host($this->backend, $this->params->getRequired('host'));
        }

        $this->applyRestriction('monitoring/filter/objects', $object);
        if ($object->fetch() === false) {
            $this->httpNotFound($this->translate('Host or Service not found'));
        }

        $graph = new Grapher();
        $this->view->graph = $graph->getPreviewHtml($object);
    }
}
