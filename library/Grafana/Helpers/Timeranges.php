<?php

namespace Icinga\Module\Grafana\Helpers;

use Icinga\Application\Icinga;

class Timeranges
{
    private $urlparams;
    private $link;
    private $view;

    private static $timeRanges = [
        'Minutes' => [
            '5m' => '5 minutes',
            '15m' => '15 minutes',
            '30m' => '30 minutes',
            '45m' => '45 minutes'
        ],
        'Hours' => [
            '1h' => '1 hour',
            '3h' => '3 hours',
            '6h' => '6 hours',
            '8h' => '8 hours',
            '12h' => '12 hours',
            '24h' => '24 hours'
        ],
        'Days' => [
            '2d' => '2 days',
            '7d' => '7 days',
            '14d' => '14 days',
            '30d' => '30 days',
        ],
        'Months' => [
            '2M' => '2 months',
            '6M' => '6 months',
            '9M' => '9 months'
        ],
        'Years' => [
            '1y' => '1 year',
            '2y' => '2 years',
            '3y' => '3 years'
        ],
        'Special' => [
            '1d/d' => 'Yesterday',
            '2d/d' => 'Day b4 yesterday',
            '1w/w' => 'Previous week',
            '1M/M' => 'Previous month',
            '1Y/Y' => 'Previous Year',
        ]
    ];

    public function __construct(array $array = [], string $link = '')
    {
        $this->urlparams = $array;
        $this->link = $link;

        $this->view = Icinga::app()->getViewRenderer()->view;
    }

    private function getTimerangeLink(string $rangeName, string $timeRange)
    {
        $this->urlparams['timerange'] = $timeRange;

        return $this->view->qlink(
            $rangeName,
            $this->link,
            $this->urlparams,
            [
                'class' => 'action-link',
                'data-base-target' => '_self',
                'title' => 'Set timerange for graph(s) to ' . $rangeName
            ]
        );
    }


    protected function isValidTimeStamp($timestamp)
    {
        return ((string) (int) $timestamp === $timestamp)
            && ($timestamp <= PHP_INT_MAX)
            && ($timestamp >= ~PHP_INT_MAX);
    }

    private function buildTimerangeMenu(string $timerange = '', string $timerangeto = '')
    {
        $url = 'grafana/icingadbdashboard?';

        $clockIcon = $this->view->qlink(
            $timerange,
            'dashboard/new-dashlet',
            ['url' => $url . http_build_query($this->urlparams, '', '&', PHP_QUERY_RFC3986)],
            ['icon' => 'clock', 'title' => 'Add graph to dashboard']
        );

        $menu = '<table class="grafana-table"><tr>';
        $menu .= '<td>' . $clockIcon . '</td>';
        foreach (self::$timeRanges as $key => $mainValue) {
            $menu .= '<td><ul class="grafana-menu-navigation"><a class="main" href="#">' . $key . '</a>';
            $counter = 1;
            foreach ($mainValue as $subkey => $value) {
                $menu .= '<li class="grafana-menu-n' . $counter . '">' . $this->getTimerangeLink(
                    $value,
                    $subkey
                ) . '</li>';
                $counter++;
            }
            $menu .= '</ul></td>';
        }

        $timerange = urldecode($timerange);
        $timerangeto = urldecode($timerangeto);

        if ($this->isValidTimeStamp($timerange)) {
            $d = new \DateTime();
            $d->setTimestamp($timerange/1000);
            $timerange = $d->format("Y-m-d H:i:s");
        }

        if ($this->isValidTimeStamp($timerangeto)) {
            $d = new \DateTime();
            $d->setTimestamp($timerangeto/1000);
            $timerangeto = $d->format("Y-m-d H:i:s");
        }

        $menu .= '</tr></table>';
        return $menu;
    }

    public function getTimerangeMenu(string $timerange = '', string $timerangeto = '')
    {
        return $this->buildTimerangeMenu($timerange, $timerangeto);
    }

    public static function getTimeranges()
    {
        return call_user_func_array('array_merge', array_values(self::$timeRanges));
    }
}
