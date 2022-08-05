<?php

$GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] .= ';{backend_notify_legend},emailSubject,emailText;';
$GLOBALS['TL_DCA']['tl_settings']['fields']['emailSubject'] = [
    'inputType' => 'text',
    'eval' => [
        'decodeEntities' => true,
        'tl_class' => 'w50'
    ]
];
$GLOBALS['TL_DCA']['tl_settings']['fields']['emailText'] = [
    'inputType' => 'textarea',
    'eval' => [
        'allowHtml' => true,
        'decodeEntities' => true,
        'tl_class' => 'clr'
    ]
];