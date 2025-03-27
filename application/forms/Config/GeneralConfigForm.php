<?php

namespace Icinga\Module\Grafana\Forms\Config;

use Icinga\Module\Grafana\Helpers\Timeranges;
use Icinga\Forms\ConfigForm;

/**
 * GeneralConfigForm is the configuration form for the module
 */
class GeneralConfigForm extends ConfigForm
{
    /**
     * Initialize this form
     */
    public function init()
    {
        $this->setName('form_config_grafana_general');
        $this->setSubmitLabel('Save Changes');
    }

    /**
     * {@inheritdoc}
     */
    public function createElements(array $formData)
    {
        $this->addElement(
            'text',
            'grafana_host',
            [
                'placeholder' => 'example server.name:3000',
                'label' => $this->translate('Host'),
                'description' => $this->translate('Host name of the Grafana server.'),
                'required' => true
            ]
        );

        $this->addElement(
            'select',
            'grafana_protocol',
            [
                'label' => 'Protocol',
                'multiOptions' => [
                    'http' => $this->translate('Insecure: http'),
                    'https' => $this->translate('Secure: https'),
                ],
                'description' => $this->translate('Protocol used to access Grafana.'),
                'class' => 'autosubmit',
            ]
        );

        // TLS configuration
        if (isset($formData['grafana_protocol']) && $formData['grafana_protocol'] === 'https') {
            $this->addElement(
                'checkbox',
                'grafana_ssl_verifypeer',
                [
                    'value' => false,
                    'label' => $this->translate('SSL verify peer'),
                    'description' => $this->translate('Verify the peer\'s SSL certificate.'),
                ]
            );

            $this->addElement(
                'checkbox',
                'grafana_ssl_verifyhost',
                [
                    'value' => false,
                    'label' => $this->translate('SSL verify host'),
                    'description' => $this->translate('Verify the certificate\'s name against host.'),
                ]
            );
        }

        // Timerange configuration
        $this->addElement(
            'select',
            'grafana_timerange',
            [
                'label' => $this->translate('Timerange'),
                'multiOptions' => array_merge(['' => 'Use default (6h)'], Timeranges::getTimeranges()),
                'description' => $this->translate('The default timerange to use for the graphs.')
            ]
        );
        $this->addElement(
            'select',
            'grafana_timerangeAll',
            [
                'label' => $this->translate('Timerange ShowAll'),
                'value' => '1w/w',
                'multiOptions' => Timeranges::getTimeranges(),
                'description' => $this->translate('The default timerange to use for show all graphs.')
            ]
        );

        // Custom Variable configuration
        $this->addElement(
            'text',
            'grafana_custvardisable',
            [
                'label' => $this->translate('Disable custom variable'),
                'description' => $this->translate('Name of the custom variable that, if set to true, will disable the graph.'),
            ]
        );
        $this->addElement(
            'text',
            'grafana_custvarconfig',
            [
                'label' => $this->translate('Config custom variable'),
                'description' => $this->translate('Name of the custom variable that, if set, hold the config name to be used.'),
            ]
        );

        // Grafana dashboard configuration
        $this->addElement(
            'text',
            'grafana_defaultdashboard',
            [
                'value' => 'icinga2-default',
                'label' => $this->translate('Default dashboard'),
                'description' => $this->translate('Name of the default dashboard.'),
            ]
        );
        $this->addElement(
            'text',
            'grafana_defaultdashboarduid',
            [
                'label' => $this->translate('Default dashboard UID'),
                'description' => $this->translate('UID of the default dashboard.'),
                'required' => true,
            ]
        );
        $this->addElement(
            'text',
            'grafana_defaultdashboardpanelid',
            [
                'value' => '1',
                'label' => $this->translate('Default panel ID(s)'),
                'description' => $this->translate('Comma separated IDs of the panels used in the default dashboard.'),
                'required' => true,
            ]
        );
        $this->addElement(
            'number',
            'grafana_defaultorgid',
            [
                'value' => '1',
                'label' => $this->translate('Default organization ID'),
                'description' => $this->translate('ID of the default organization.'),
            ]
        );

        // Graphs shadow configuration
        $this->addElement(
            'checkbox',
            'grafana_shadows',
            [
                'value' => false,
                'label' => $this->translate('Show shadows'),
                'description' => $this->translate('Show shadows around the graph.'),
            ]
        );

        // Grafana datasource configuration
        $this->addElement(
            'select',
            'grafana_datasource',
            [
                'label' => $this->translate('Datasource type'),
                'multiOptions' => [
                    'influxdb' => $this->translate('InfluxDB'),
                    'graphite' => $this->translate('Graphite'),
                ],
                'description' => $this->translate('Select the Grafana datasource.')
            ]
        );

        // Grafana access mode configuration
        $this->addElement(
            'select',
            'grafana_accessmode',
            [
                'label' => $this->translate('Grafana access'),
                'multiOptions' => [
                    'indirectproxy' => $this->translate('Indirect proxy'),
                    'iframe' => $this->translate('iFrame'),
                ],
                'description' => $this->translate('User access Grafana directly or module proxies graphs.'),
                'class' => 'autosubmit',
                'required' => true
            ]
        );

        // Indirect proxy mode configuration
        if (isset($formData['grafana_accessmode']) && $formData['grafana_accessmode'] === 'indirectproxy') {
            $this->addElement(
                'number',
                'grafana_proxytimeout',
                [
                    'label' => $this->translate('Proxy timeout'),
                    'placeholder' => '5',
                    'description' => $this->translate('Timeout in seconds for proxy mode to fetch images.')
                ]
            );
            $this->addElement(
                'select',
                'grafana_authentication',
                [
                    'label' => $this->translate('Authentication type'),
                    'value' => 'anon',
                    'multiOptions' => [
                        'anon' => $this->translate('Anonymous'),
                        'token' => $this->translate('Service Account'),
                        'basic' => $this->translate('Username & Password'),
                    ],
                    'description' => $this->translate('Authentication type used for Grafana access.'),
                    'class' => 'autosubmit'
                ]
            );

            // Indirect proxy mode authentication configuration
            if (isset($formData['grafana_authentication']) && $formData['grafana_authentication'] === 'basic') {
                    $this->addElement(
                        'text',
                        'grafana_username',
                        [
                            'label' => $this->translate('Username'),
                            'description' => $this->translate('The HTTP Basic Auth user name used to access Grafana.'),
                            'required' => true
                        ]
                    );
                    $this->addElement(
                        'password',
                        'grafana_password',
                        [
                            'renderPassword' => true,
                            'label' => $this->translate('Password'),
                            'description' => $this->translate('The HTTP Basic Auth password used to access Grafana.'),
                            'required' => true
                        ]
                    );
            } elseif (isset($formData['grafana_authentication']) && $formData['grafana_authentication'] === 'token') {
                $this->addElement(
                    'password',
                    'grafana_apitoken',
                    [
                        'renderPassword' => true,
                        'label' => $this->translate('API Token'),
                        'description' => $this->translate('The Service Account token used to access Grafana.'),
                        'required' => true
                    ]
                );
            }
        }

        // Indirect proxy mode cache configuration
        if (isset($formData['grafana_accessmode']) && $formData['grafana_accessmode'] === 'indirectproxy') {
            $this->addElement(
                'select',
                'grafana_enablecache',
                [
                    'label' => $this->translate('Enable cache'),
                    'value' => 'yes',
                    'multiOptions' => [
                        'yes' => $this->translate('Yes'),
                        'no' => $this->translate('No'),
                    ],
                    'description' => $this->translate('Enable cache on indirect proxy mode.')
                ]
            );
        }

        // iFrame mode configuration
        if (isset($formData['grafana_accessmode']) && ( $formData['grafana_accessmode'] != 'iframe' )) {
            $this->addElement(
                'number',
                'grafana_height',
                [
                    'value' => '280',
                    'label' => $this->translate('Graph height'),
                    'description' => $this->translate('The default graph height in pixels.')
                ]
            );
            $this->addElement(
                'number',
                'grafana_width',
                [
                    'value' => '640',
                    'label' => $this->translate('Graph width'),
                    'description' => $this->translate('The default graph width in pixels.')
                ]
            );
            $this->addElement(
                'select',
                'grafana_usepublic',
                [
                    'label' => $this->translate('Use public links'),
                    'value' => 'no',
                    'multiOptions' => [
                        'yes' => $this->translate('Yes'),
                        'no' => $this->translate('No'),
                    ],
                    'description' => $this->translate('Use public URL that is different from host above.'),
                    'class' => 'autosubmit'
                ]
            );
        }

        // iFrame mode public link configuration
        if (isset($formData['grafana_usepublic']) && ( $formData['grafana_usepublic'] === 'yes' ) && ( $formData['grafana_accessmode'] != 'iframe' )) {
            $this->addElement(
                'text',
                'grafana_publichost',
                [
                    'placeholder' => 'example server.name:3000',
                    'label' => $this->translate('Public host'),
                    'description' => $this->translate('Public host name of the Grafana server.'),
                    'required' => true
                ]
            );
            $this->addElement(
                'select',
                'grafana_publicprotocol',
                [
                    'label' => 'Public protocol',
                    'multiOptions' => [
                        'http' => $this->translate('Insecure: http'),
                        'https' => $this->translate('Secure: https'),
                    ],
                    'description' => $this->translate('Public protocol used to access Grafana.'),
                ]
            );
        }

        if (isset($formData['grafana_accessmode']) && ( $formData['grafana_accessmode'] === 'iframe' )) {
            $this->addElement(
                'checkbox',
                'grafana_jwtEnable',
                [
                    'label' => $this->translate('Enable JWT'),
                    'value' => false,
                    'description' => $this->translate('Enable JWT. Grafana host will receive the JWT token to authorize the user.'),
                    'class' => 'autosubmit',
                ]
            );
            if (isset($formData['grafana_jwtEnable'])) {
                $this->addElement(
                    'number',
                    'grafana_jwtExpires',
                    [
                        'label' => $this->translate('JWT Expiration'),
                        'placeholder' => 30,
                        'description' => $this->translate('JWT Token expiration in seconds. A very short time is recommended. Default 30 seconds.'),
                        'required' => false,
                        'class' => 'autosubmit',
                    ]
                );
                $this->addElement(
                    'text',
                    'grafana_jwtIssuer',
                    [
                        'placeholder' => 'https://localhost',
                        'label' => $this->translate('JWT Issuer'),
                        'description' => $this->translate('The issuer of the token (e.g. URL of this system). Can be used as a validation when other systems receive the token. Default is empty, no issuer.'),
                        'required' => false,
                        'class' => 'autosubmit',
                    ]
                );
                $this->addElement(
                    'text',
                    'grafana_jwtUser',
                    [
                        'placeholder' => 'username',
                        'label' => $this->translate('JWT Subject (login)'),
                        'description' => $this->translate('The username or email to be used as login. Leave empty to use the Icinga Web username.'),
                        'required' => false,
                        'class' => 'autosubmit',
                    ]
                );
            }
        }
    }
}
