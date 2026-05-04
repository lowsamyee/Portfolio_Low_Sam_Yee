<?php 

require_once("vendor/autoload.php");


$test_keys = array(
 "publishable_key" => "",
 "secret_key" => ""
);

\Stripe\Stripe::SetApiKey($test_keys['secret_key']);



 ?>