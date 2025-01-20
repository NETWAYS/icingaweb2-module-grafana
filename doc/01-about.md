# About

Add Grafana graphs into Icinga Web 2 to display performance metrics.

![Icinga Web 2 Grafana Integration](images/icingaweb2_grafana_screenshot_01.png "Grafana")
![Icinga Web 2 Grafana Integration](images/icingaweb2_grafana_screenshot_02.png "Grafana")

## Graphs for dashboards

![Icinga Web 2 Grafana Integration](images/icingaweb2_grafana_screenshot_06.png)

To add a graph to your dashboard click on the small clock left of the timerange menu of a graph.

## Permissions

The module provides the following permissions:

* `grafana/graphconfig`, Allow to configure graphs.
* `grafana/graph`, Allow to view graphs in dashboards.
* `grafana/showall`, Allow access to see all graphs of a host.
* `grafana/showlink` Display a link to the Grafana instance.
* `grafana/debug` Allow to see debug information for graphs (can be accessed via the URL parameter `&grafanaDebug`).

## Show all graphs

To see all graphs on one page, click on the `Show all graphs` link in a host object view.
![Show all graph](images/showallgraph.png "Show all graph")

## License

Icinga Web 2 and this Icinga Web 2 module are licensed under the terms of the GNU
General Public License Version 2, you will find a copy of this license in the
LICENSE file included in the source package.

## Support

[GitHub Issues](https://github.com/NETWAYS/icingaweb2-module-grafana/issues)

## Thanks

Thanks for bug reporting/fixing or enhancements goes to:

* Thilo W.
* Robert G.
* Peter N.
* Alex
* Michael F.
* Martin N.
* Emerson P.
* Markus (mj84)
* Matthias (matthiaswe)
* Jon B.
* Nicolai B.
* Eric L.
* Benjamin S.
* Lasse W. & Ruben M.
* Bernhard F. & Garrett S.
* raviks789 (IcingaDB support)
