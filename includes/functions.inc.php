<?php
/**
* This function returns the value of a config key
*
* @param string $key --> The key of the config option to get
*
* @return Mixed --> Returns value of config key if found else returns Exception
*/
function getConfig(string $key){
  if(isset(CONFIG[$key])){
    return CONFIG[$key];
  } else {
    throw new Exception("Key: '{$key}' not found in config");
  }
}

/**
* This function logs (echos) something with a line break after it
*
* @param string $log --> The string to log
*
* @return bool
*/
function _log(string $log){
  echo($log . "<br />");
  return true;
}

/**
* Write errors to json file
*
* @param array $errors --> Array of failed connections to save to file
*
* @return bool
*/
function writeErrors(array $errors){
  return file_put_contents(ROOT_PATH.'/_cache/errors.json', json_encode($errors));
}

/**
* Read errors from json file
*
* @param array $errors --> Array of failed connections to save to file
*
* @return array
*/
function readErrors(){
  if(file_exists(ROOT_PATH.'/_cache/errors.json')){
    $errors = json_decode(file_get_contents(ROOT_PATH.'/_cache/errors.json'), true);
  } else {
    $errors = array();
  }

  return $errors;
}

/**
* Sends a whatsapp message via the niceapi from niceapi.net
*
* @param string $phoneNumber --> Phone number to send whatsapp to
* @param string $message --> Message to send
*
* @return bool
*/
function sendWhatsapp(string $phoneNumber, string $message){
  $ch = curl_init();

  curl_setopt($ch, CURLOPT_URL, 'https://niceapi.net/API');
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $message);
  curl_setopt($ch, CURLOPT_POST, 1);

  $requestHeaders = array(
    'X-APIId: '.getConfig('NICEAPI')['XAPI_ID'],
    'X-APIMobile: '.$phoneNumber,
    'Content-Type: application/x-www-form-urlencoded',
  );

  curl_setopt($ch, CURLOPT_HTTPHEADER, $requestHeaders);

  $response = curl_exec($ch);
  if (curl_errno($ch)) {
      _log("Error sending WhatsApp to: '{$phoneNumber}' Due to curl error: '".curl_error($ch)."''");
      return false;
  } else {
    _log("Sending WhatsApp to: '{$phoneNumber}'");
    _log("cURL response: '{$response}'");
  }
  curl_close($ch);

  if($response !== "queued"){
    return false;
  }

  return true;
}

/**
* Compares two arrays to see if they are identical
*
* @param array $arr1 --> First array to use for comparison
* @param array $arr2 --> Second array to use for comparison
*
* @return bool --> True if arrays are identical
*/
function identicalArray(array $arr1, array $arr2) {
    return $arr1 == $arr2;
}
