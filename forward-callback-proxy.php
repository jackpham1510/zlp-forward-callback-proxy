<?php

# PHP Version 7.3.3

error_reporting(E_ALL ^ E_WARNING);

try {
  $postdatajson = file_get_contents('php://input');
  $postdata = json_decode($postdatajson, true);
  $data = json_decode($postdata["data"], true);
  $embeddata = json_decode($data["embeddata"], true);
  $url = $embeddata["forward_callback"];

  if (empty($url)) {
    throw new Exception("Callback url is empty");
  }

  $context = stream_context_create([
    "http" => [
      "method" => "POST",
      "header" => "Content-type: application/json\r\n".
                  "Accept: application/json\r\n",
      "timeout" => 180, # 3 phút
      "content" => $postdatajson
    ]
  ]);
  
  $result = file_get_contents($url, false, $context);
  if ($result) {
    echo $result;
  } else {
    throw new Exception("No response received");
  }
} catch (Exception $e) {
  echo json_encode([
    "returncode" => '0', # ZaloPay server sẽ callback lại tối đa 3 lần
    "returnmessage" => $e->getMessage()
  ]);
}