<?php

if(isset($_GET['token'])){

$token = $_GET['token'];

$part = explode(".",$token);
$header = $part[0];
$payload = $part[1];
$signature = $part[2];

$x = json_decode(
  base64_decode($payload)
);

if ($x->exp < date("Y-m-d")) {
  echo "ok";
}
}
?>
