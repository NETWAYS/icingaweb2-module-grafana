<?php

namespace Icinga\Module\Grafana\ProvidedHook\Icingadb;

use Icinga\Application\Hook\ConfigFormEventsHook;
use Icinga\Module\Grafana\Forms\Config\GeneralConfigForm;
use Icinga\Web\Form;
use Icinga\Module\Grafana\Helpers\JwtToken;

class GeneralConfigFormHook extends ConfigFormEventsHook
{

    public function appliesTo(Form $form)
    {
        return $form instanceof GeneralConfigForm;
    }

    public function onSuccess(Form $form)
    {
        $enable = $form->getElement('grafana_jwtEnable');

        if (isset($enable) && $enable->getValue()) {
            JwtToken::generateRsaKeys();
        }
    }
}
