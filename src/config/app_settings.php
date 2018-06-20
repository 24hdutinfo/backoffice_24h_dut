<?php
/**
 * File:  api_settings.php
 * Creation Date: 03/02/2017
 * description:
 *
 * @author: canals
 */



if (\app24\bootstrap\AppBootstrap::getMode() === \app24\bootstrap\AppBootstrap::MOD_DEV )
    return [
    'settings' => [
        'displayErrorDetails' => true ,
        'baseurl'=> 'http://24h.local:1680',
        'db'=> parse_ini_string(file_get_contents(__DIR__ . '/24h.app.db.dev.ini')),
        'mailer'=> parse_ini_string(file_get_contents(__DIR__ . '/24h.app.mail.dev.ini')),
        'upload_dir'=> __DIR__ . '/../../log',
        'template_dir' => [__DIR__ . '/../app24/templates/24h_b', __DIR__ . '/../app24/templates/admin'],
        'show_team_members_name'=>true
    ]
    ] ;

if (\app24\bootstrap\AppBootstrap::getMode() === \app24\bootstrap\AppBootstrap::MOD_PROD)
    return [
        'settings' => [
            'displayErrorDetails' => false ,
            'baseurl'=>'https://24hinfo-iutnc.univ-lorraine.fr',
            'db'=> parse_ini_string(file_get_contents(__DIR__ . '/24h.app.db.prod.ini')),
            'mailer'=> parse_ini_string(file_get_contents(__DIR__ . '/24h.app.mail.prod.ini')),
            'upload_dir'=> __DIR__ . '/../../log',
            'template_dir' => [__DIR__ . '/../app24/templates/24h_b', __DIR__ . '/../app24/templates/admin'],
            'show_team_members_name'=>false
        ]
    ] ;

