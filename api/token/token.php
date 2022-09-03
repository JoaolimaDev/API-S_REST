<?php
function base64ErlEncode($data)
{
  return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
}
$header = json_encode( [
   'alg' => 'HS256',
   'typ' => 'JWT'
]);

$header = base64ErlEncode($header);

$payload = json_encode([
   'iss' => 'PMG',
   'name' => 'pmg-entities',
   'exp'=> date("Y-m-d", strtotime("+1 day"))
]);

$payload = base64ErlEncode($payload);

$signature = hash_hmac('sha256',"$header.$payload",md5('98-54jdfi$'),true);
$signature = base64ErlEncode($signature);

echo "$header.$payload.$signature";

 ?>
