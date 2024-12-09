# Change Log
## [v3.0.0](https://github.com/NETWAYS/icingaweb2-module-grafana/tree/v3.0.0) (2024-12-10)

**BREAKING CHANGES:**

- Remove `enablelink` configuration and permission

A link to the Grafana instance via a small icon is now always
added to the header in both proxy and iframe mode.

- Rework Caching and rename `indirectproxyrefresh` to `enablecaching`

The use of HTTP caching has been simplified.
It now uses the check interval to determine how long a graph's image is cached.

**Features**
- PHP 8.2 and PHP 8.3 Support
- Support strict CSP
- Dark/Light Theme depending on user's settings (removed the `theme` configuration option)
- Debug information can be requested via the `debugGrafana` URL parameter (removed the `debug` configuration option)

**Fixes**
- Use of special characters in custom variables now works
- Update InfluxDB dashboards for Grafana 11.3.0
- Remove unused `defaultdashboardstore` and `version` configuration options

## [v2.0.3](https://github.com/mikesch-mp/icingaweb2-module-grafana/tree/v2.0.3) (2023-04-06)
**Fixes**
- Services can now be added to dashlets again (#309)

## [v2.0.2](https://github.com/mikesch-mp/icingaweb2-module-grafana/tree/v2.0.2) (2023-04-06)
**Fixes**
- Removed unused old hook reference
- Removed border in iframe mode

## [v2.0.1](https://github.com/mikesch-mp/icingaweb2-module-grafana/tree/v2.0.1) (2023-03-07)
**Fixes**
- Fix for ignored custvarconfig variable in indirect proxy mode
- Fix show all graphs for host

## [v2.0.0](https://github.com/mikesch-mp/icingaweb2-module-grafana/tree/v2.0.0) (2023-02-23)

**BREAKING CHANGES:**
- Only [icingadb](https://github.com/Icinga/icingadb) backend for [icingadb-web](https://github.com/Icinga/icingadb-web) will be supported from now on.

*Open Issues*

- PDF export does not load the panels
- Documentation needs to be rewritten
