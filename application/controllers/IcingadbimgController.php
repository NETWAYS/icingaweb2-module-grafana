<?php

namespace Icinga\Module\Grafana\Controllers;

use Icinga\Exception\NotFoundError;
use Icinga\Module\Grafana\Web\Controller\IcingadbGrafanaController;
use Icinga\Application\Config;
use Icinga\Exception\ConfigurationError;
use Icinga\Authentication\Auth;
use Icinga\Module\Grafana\Helpers\Util;
use Icinga\Module\Icingadb\Model\CustomvarFlat;
use Icinga\Module\Icingadb\Model\Host;
use Icinga\Module\Icingadb\Model\Service;
use ipl\Stdlib\Filter;
use ipl\Web\Url;

class IcingadbimgController extends IcingadbGrafanaController
{
    protected $host;
    protected $service;
    protected $timerange;
    protected $myConfig;
    protected $myAuth;
    protected $authentication;
    protected $grafanaHost             = null;
    protected $grafanaTheme            = null;
    protected $protocol                = "http";
    protected $username                = null;
    protected $password                = null;
    protected $apiToken                = null;
    protected $width                   = 640;
    protected $height                  = 280;
    protected $defaultDashboard        = "icinga2-default";
    protected $defaultDashboardPanelId = "1";
    protected $defaultOrgId            = "1";
    protected $shadows                 = false;
    protected $defaultDashboardStore   = "db";
    protected $dataSource              = null;
    protected $proxyTimeout            = "5";
    protected $custvarconfig           = "grafana_graph_config";
    protected $SSLVerifyPeer           = false;
    protected $SSLVerifyHost           = "0";
    protected $cacheTime               = 300;
    protected $defaultdashboarduid;
    protected $enableCache             = "yes";
    protected $customVars;
    protected $timerangeto;
    protected $object;
    protected $dashboard;
    protected $dashboarduid;
    protected $panelId;
    protected $orgId;

    public function init()
    {
        if (! $this->useIcingadbAsBackend) {
            $this->redirectNow(Url::fromPath('grafana/dashboard')->setQueryString($this->params));
        }
        /* we need at least a host name */
        if (is_null($this->getParam('host'))) {
            throw new \Error('No host given!');
        }

        /* save timerange from params for later use */
        $this->timerange = $this->hasParam('timerange') ? urldecode($this->getParam('timerange')) : null;
        if ($this->hasParam('timerangeto')) {
            $this->timerangeto = urldecode($this->getParam('timerangeto'));
        } else {
            $this->timerangeto = strpos($this->timerange ?? '', '/') ? 'now-' . $this->timerange ?? '' : "now";
        }

        // Get the cachetime URL parameter and use the default if not present
        $this->cacheTime = $this->hasParam('cachetime') ? $this->getParam('cachetime') : $this->cacheTime;

        /* load global configuration */
        $this->myConfig = Config::module('grafana')->getSection('grafana');
        $this->grafanaHost = $this->myConfig->get('host', $this->grafanaHost);
        if ($this->grafanaHost == null) {
            throw new ConfigurationError(
                'No Grafana host configured!'
            );
        }
        $this->protocol = $this->myConfig->get('protocol', $this->protocol);

        $this->defaultDashboard = $this->myConfig->get('defaultdashboard', $this->defaultDashboard);
        $this->defaultdashboarduid = $this->myConfig->get('defaultdashboarduid', null);
        if (is_null($this->defaultdashboarduid)) {
            throw new ConfigurationError(
                'Usage of Grafana 5 is configured but no UID for default dashboard found!'
            );
        }
        $this->defaultDashboardPanelId = $this->myConfig->get(
            'defaultdashboardpanelid',
            $this->defaultDashboardPanelId
        );
        $this->defaultOrgId = $this->myConfig->get('defaultorgid', $this->defaultOrgId);
        $this->grafanaTheme = $this->getUserThemeMode();
        $this->defaultDashboardStore = $this->myConfig->get('defaultdashboardstore', $this->defaultDashboardStore);
        $this->height = $this->myConfig->get('height', $this->height);
        $this->width = $this->myConfig->get('width', $this->width);
        $this->proxyTimeout = $this->myConfig->get('proxytimeout', $this->proxyTimeout);
        $this->enableCache = $this->myConfig->get('enablecache', $this->enableCache);
        /**
         * Read the global default timerange
         */
        if ($this->timerange == null) {
            $this->timerange = $this->config->get('timerange', $this->timerange);
        }
        /**
         * Datasource needed to regex special chars
         */
        $this->dataSource = $this->myConfig->get('datasource', $this->dataSource);
        /**
         * Display shadows around graph
         */
        $this->shadows = $this->myConfig->get('shadows', $this->shadows);
        /**
         * Name of the custom variable for graph config
         */
        $this->custvarconfig = ($this->myConfig->get('custvarconfig', $this->custvarconfig));
        /**
         * Verify the certificate's name against host
         */
        $this->SSLVerifyHost = ($this->myConfig->get('ssl_verifyhost', $this->SSLVerifyHost));
        /**
         * Verify the peer's SSL certificate
         */
        $this->SSLVerifyPeer = ($this->myConfig->get('ssl_verifypeer', $this->SSLVerifyPeer));

        /**
         * Username & Password or token
         */

        $this->apiToken = $this->myConfig->get('apitoken', $this->apiToken);
        $this->authentication = $this->myConfig->get('authentication');
        if ($this->apiToken == null && $this->authentication == "token") {
            throw new ConfigurationError(
                'API token usage configured, but no token given!'
            );
        } else {
            $this->username = $this->myConfig->get('username', $this->username);
            $this->password = $this->myConfig->get('password', $this->password);
            if ($this->username != null) {
                if ($this->password != null) {
                    $this->myAuth = $this->username . ":" . $this->password;
                } else {
                    $this->myAuth = $this->username;
                }
            } else {
                $this->myAuth = "";
            }
        }
    }

    public function indexAction()
    {
        if (! $this->useIcingadbAsBackend) {
            $this->redirectNow(Url::fromPath('grafana/img')->setQueryString($this->params));
        }

        $varsFlat = CustomvarFlat::on($this->getDb());

        $this->applyRestrictions($varsFlat);

        $varsFlat
            ->columns(['flatname', 'flatvalue'])
            ->orderBy('flatname');

        if ($this->hasParam('service') && ! is_null($this->getParam('service'))) {
            $service = $this->getServiceObject();
            $this->object = $service;
            $serviceName = $this->object->name;
            $hostName = $this->object->host->name;
            $varsFlat->filter(Filter::equal('service.id', $this->object->id));
        } else {
            $host = $this->getHostObject();
            $this->object = $host;
            $serviceName = $this->object->checkcommand_name;
            $hostName = $this->object->name;
            $varsFlat->filter(Filter::equal('host.id', $this->object->id));
        }

        $customVars = $this->getDb()->fetchPairs($varsFlat->assembleSelect());

        if (array_key_exists($this->custvarconfig, $customVars) && !empty($customVars[$this->custvarconfig])) {
            $this->setGraphConf($customVars[$this->custvarconfig]);
        } else {
            $this->setGraphConf($serviceName, $this->object->checkcommand_name);
        }

        if (!empty($this->customVars)) {
            foreach ($customVars as $k => $v) {
                $search[] = "\$$k\$";
                $replace[] = is_string($v) ? $v : null;
                $this->customVars = str_replace($search, $replace, $this->customVars);
            }

            // urlencode values
            $customVars = "";
            foreach (preg_split('/\&/', $this->customVars, -1, PREG_SPLIT_NO_EMPTY) as $param) {
                $arr = explode("=", $param);
                if (preg_match('/^\$.*\$$/', $arr[1])) {
                    $arr[1] = '';
                }
                if ($this->dataSource == "graphite") {
                    $arr[1] = Util::graphiteReplace($arr[1]);
                }
                $customVars .= '&' . $arr[0] . '=' . rawurlencode($arr[1]);
            }
            $this->customVars = $customVars;
        }
        // replace special chars for graphite
        if ($this->dataSource == "graphite") {
            $serviceName = Util::graphiteReplace($serviceName);
            $hostName = Util::graphiteReplace($hostName);
        }

        $imageHtml = "";
        $result = $this->getMyimageHtml($serviceName, $hostName, $imageHtml);

        if ($this->enableCache === 'yes') {
            header('Cache-Control: public, max-age=' . $this->cacheTime);
        }

        header("Content-type: image/png");

        if (! $result) {
            $string = wordwrap($this->translate('Error'). ': ' . $imageHtml, 40, "\n");
            $lines = explode("\n", $string);
            $im = @imagecreate($this->width, $this->height);
            $background_color = imagecolorallocate($im, 255, 255, 255); //white background
            $text_color = imagecolorallocate($im, 255, 0, 0);//black text
            foreach ($lines as $i => $line) {
                imagestring($im, 5, 0, 5 + $i * 15, $line, $text_color);
            }

            ob_start();
            imagepng($im);
            $img = ob_get_contents();
            ob_end_clean();
            imagedestroy($im);

            print $img;
        } else {
            print $imageHtml;
        }
        exit;
    }

    private function getHostObject()
    {
        $query = Host::on($this->getDb())->with(['state', 'icon_image']);
        $query->filter(Filter::equal('name', urldecode($this->getParam('host'))));

        $this->applyRestrictions($query);

        $host = $query->first();
        if ($host === null) {
            throw new NotFoundError(t('Service not found'));
        }

        return $host;
    }

    private function getServiceObject()
    {
        $query = Service::on($this->getDb())->with([
            'state',
            'icon_image',
            'host',
            'host.state'
        ]);
        $query->filter(Filter::equal('name', urldecode($this->getParam('service'))));
        $query->filter(Filter::equal('host.name', urldecode($this->getParam('host'))));

        $this->applyRestrictions($query);

        /** @var Service $service */
        $service = $query->first();
        if ($service === null) {
            throw new NotFoundError(t('Service not found'));
        }

        return $service;
    }

    private function setGraphConf($serviceName, $serviceCommand = null)
    {
        $graphConfig = Config::module('grafana', 'graphs');

        if ($graphConfig->hasSection(strtok($serviceName, ' ')) && ($graphConfig->hasSection($serviceName) == false)) {
            $serviceName = strtok($serviceName, ' ');
        }

        if ($graphConfig->hasSection(strtok($serviceName, ' ')) == false
            && ($graphConfig->hasSection($serviceName) == false)
        ) {
            $serviceName = $serviceCommand;
            if ($graphConfig->hasSection($serviceCommand) == false && $this->defaultDashboard == 'none') {
                return null;
            }
        }

        $this->dashboard = $graphConfig->get($serviceName, 'dashboard', $this->defaultDashboard);
        $this->dashboarduid = $graphConfig->get($serviceName, 'dashboarduid', $this->defaultdashboarduid);
        $this->panelId = $this->hasParam('panelid') ?
            $this->getParam('panelid')
            : $graphConfig->get($serviceName, 'panelId', $this->defaultDashboardPanelId);
        $this->orgId = $graphConfig->get($serviceName, 'orgId', $this->defaultOrgId);
        $this->customVars = $graphConfig->get($serviceName, 'customVars', '');
        $this->height = $graphConfig->get($serviceName, 'height', $this->height);
        $this->width = $graphConfig->get($serviceName, 'width', $this->width);
    }

    private function getMyimageHtml($serviceName, $hostName, &$imageHtml)
    {
        $imgClass = $this->shadows ? "grafana-img grafana-img-shadows" : "grafana-img";
        // Test whether curl is loaded
        if (extension_loaded('curl') === false) {
            $imageHtml = $this->translate('CURL extension is missing.'
                . ' Please install CURL for PHP and ensure it is loaded.');
            return false;
        }

        $pngUrl = sprintf(
            '%s://%s/render/d-solo/%s/%s?var-hostname=%s&var-service=%s&var-command=%s%s&panelId=%s&orgId=%s'
            . '&width=%s&height=%s&theme=%s&from=%s&to=%s',
            $this->protocol,
            $this->grafanaHost,
            $this->dashboarduid,
            $this->dashboard,
            rawurlencode($hostName),
            rawurlencode($serviceName),
            rawurlencode($this->object->checkcommand_name),
            $this->customVars,
            $this->panelId,
            $this->orgId,
            $this->width,
            $this->height,
            $this->grafanaTheme,
            urlencode($this->timerange),
            urlencode($this->timerangeto)
        );

        // fetch image with curl
        $curl_handle = curl_init();
        $curl_opts = [
            CURLOPT_URL => $pngUrl,
            CURLOPT_CONNECTTIMEOUT => 2,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => $this->SSLVerifyPeer,
            CURLOPT_SSL_VERIFYHOST => ($this->SSLVerifyHost) ? 2 : 0,
            CURLOPT_TIMEOUT => $this->proxyTimeout,
        ];

        if ($this->authentication == "token") {
            $curl_opts[CURLOPT_HTTPHEADER] = [
                'Content-Type: application/json',
                "Authorization: Bearer ". $this->apiToken
            ];
        } else {
            $curl_opts[CURLOPT_USERPWD] = "$this->myAuth";
        }

        curl_setopt_array($curl_handle, $curl_opts);
        $result = curl_exec($curl_handle);

        $statusCode = curl_getinfo($curl_handle, CURLINFO_HTTP_CODE);

        if ($result === false) {
            $imageHtml .=$this->translate('Cannot fetch graph with curl') .': '. curl_error($curl_handle). '.';

            // Provide a hint for 'Failed to connect to ...: Permission denied'
            if (curl_errno($curl_handle) == 7) {
                $imageHtml .= $this->translate('Check SELinux or firewall');
            }
            return false;
        }

        if ($statusCode > 299) {
            $error = @json_decode($result);
            $imageHtml .= $this->translate('Cannot fetch Grafana graph')
                . ": "
                . Util::httpStatusCodeToString($statusCode)
                . " "
                . $statusCode
                . ": "
                . ($error !== null && property_exists($error, 'message') ? $error->message : "");
            return false;
        }

        curl_close($curl_handle);
        $imageHtml = $result;
        return true;
    }

    /**
     * getUserThemeMode returns the users configured Theme Mode.
     * Since we cannot handle the 'system' setting (it's client-side),
     * we default to 'dark'.
     * @return string
     */
    private function getUserThemeMode(): string
    {
        $mode = 'dark';

        if ($user = Auth::getInstance()->getUser()) {
                $mode = $user->getPreferences()->getValue('icingaweb', 'theme_mode', $mode);
        }

        // Could be system, which we cannot handle since it's browser-side
        if (!in_array($mode, ['dark', 'light'])) {
            $mode = 'dark';
        }

        return $mode;
    }
}
