<?php

$settings[ 'USE_TAGS' ] = FALSE;

$settings[ 'DIRECTORY' ] = str_replace ( $_SERVER[ 'DOCUMENT_ROOT' ], '', DR ).'/';

$settings[ 'PATH' ] = $_SERVER[ "DOCUMENT_ROOT" ] . $settings[ 'DIRECTORY' ];

$settings[ 'IP' ] = $_SERVER['HTTP_HOST'];

$settings[ 'LIVE' ] = FALSE;
