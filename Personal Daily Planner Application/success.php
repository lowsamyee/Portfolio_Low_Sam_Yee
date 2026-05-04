<?php 
session_start();

require_once("config.php");


$token = $_POST['stripeToken'];
$email = $_POST['stripeEmail'];


$customer = \Stripe\Customer::create([
    'source' => $token,
    'email' => $email
]);


$n =  $_SESSION['total_price'];

$n = $n * 100;



$charge = \Stripe\Charge::create([
  "customer" => $customer->id,
  "amount" => $n,
  "currency" => "usd"
]);

echo "You have successfully Make Payment of $n";
header("Location:./index.php");


 ?>