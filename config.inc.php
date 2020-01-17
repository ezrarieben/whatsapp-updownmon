<?php
define('CONFIG', array(
  'CONNECTION_TIMEOUT' => 5, // Timeout for port scan (Default: 5s)
  'NICEAPI' => array(
    'XAPI_ID' => '[NICEAPI API ID/KEY]', // Register API Key on: https://niceapi.net/
  ),
  'PHONE_NUMBERS' => array(
    '[PHONE NUMBER INCL. EXTENSION]' // e.g.: +41000000000 for swiss numbers
  ),
  'SERVERS' => array(
    '[NAME TO IDENTIFY SERVER]' => array(
      'IP' => '[HOSTNAME/IP]',
      'PORTS' => array(
        '[PORT NUMBER]'
      ),
    )
  ),
));
