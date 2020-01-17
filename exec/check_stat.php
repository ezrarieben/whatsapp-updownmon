<?php
define('ROOT_PATH', dirname(__DIR__));

// Remove php.ini script execution time limit
set_time_limit(0);

// Load config
require_once ROOT_PATH.'/config.inc.php';
// Load functions
require_once ROOT_PATH.'/includes/functions.inc.php';

$servers = getConfig('SERVERS');

$errors = array();

// Check servers
foreach($servers as $serverName => $server){

  _log("============================================================================");
  _log($serverName);
  _log("============================================================================");
  _log("Scanning ports: ".implode(', ', $server['PORTS'])." on IP: '{$server['IP']}'");
  _log("---");

  // Scan ports
  foreach($server['PORTS'] as $port){
    $portName = (getservbyport($port, 'tcp') !== false) ? " (".getservbyport($port, 'tcp').") " : ' ';
    $socket = fsockopen($server['IP'], $port, $errno, $errstr, getConfig('CONNECTION_TIMEOUT'));

    if($socket !== false){
      if (is_resource($socket)) {
        // Connection successful
        _log("Connection on port: '{$port}'{$portName}successful!");
      } else {
        // Connection failed
        $errors[$serverName] = $port;
        _log("Connection on port: '{$port}'{$portName}FAILED!");
      }

      // Close socket
      fclose($socket);
    } else {
      // Socket timed out
      $errors[$serverName] = $port;
      _log("Connection on port: '{$port}'{$portName}FAILED due to timeout!");
    }
  }

  _log("---");
}

// Read previous errors for comparison
$previousErrors = readErrors();

// Save current errors for comparing errors in next script call
if(file_exists(ROOT_PATH.'/_cache/errors.json') && !is_writeable(ROOT_PATH.'/_cache/errors.json')){
  die("Cant write to errors cache file. Check write permissions on cache folder and try again.");
}

// Send whatsapp messages if errors occured previously but no longer occur
if(empty($errors) && !empty($previousErrors)){
  $message = "*whatsapp-updownmon:*\n\n";
  $message .= "All systems back ONLINE!\n";

  $phoneNumbers = getConfig('PHONE_NUMBERS');

  foreach($phoneNumbers as $phoneNumber){
    if(sendWhatsapp($phoneNumber, $message)){
      writeErrors($errors);
    } else {
      _log("Failed to send WhatsApp to: '{$phoneNumber}'");
    }
  }
}

// Send whatsapp error messages if new errors occured
if(!empty($errors) && !identicalArray($previousErrors, $errors)){
  $errorMessage = "*whatsapp-updownmon:*\n\n";

  // Show previous errors if monitor had errors previously
  if(!empty($previousErrors)){
    $errorMessage .= "_Previously unreachable:_\n";

    foreach($previousErrors as $serverName => $port){
      $portName = (getservbyport($port, 'tcp') !== false) ? " (".getservbyport($port, 'tcp').") " : ' ';
      $errorMessage .= $serverName." unreachable on port: {$port}{$portName}\n";
    }

    $errorMessage .= "\n";
  }

  $errorMessage .= "_Currently unreachable:_\n";
  foreach($errors as $serverName => $port){
    $portName = (getservbyport($port, 'tcp') !== false) ? " (".getservbyport($port, 'tcp').") " : ' ';
    $errorMessage .= $serverName." unreachable on port: {$port}{$portName}\n";
  }

  $phoneNumbers = getConfig('PHONE_NUMBERS');

  foreach($phoneNumbers as $phoneNumber){
    if(sendWhatsapp($phoneNumber, $errorMessage)){
      writeErrors($errors);
    } else {
      _log("Failed to send WhatsApp to: '{$phoneNumber}'");
    }
  }
}
