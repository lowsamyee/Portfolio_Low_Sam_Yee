
<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head>
	<title>Stripe Payment</title>
</head>
<body>

	<?php require_once("config.php");

     
$n =  $_SESSION['total_price'];

$n = $n * 100;


	 ?>

	<form action="success.php" method="post">
		<script type="text/javascript" src="https://checkout.stripe.com/checkout.js" class="stripe-button"
		data-key="<?php echo $test_keys['publishable_key']; ?>" 
		data-name="Online Payment"
		data-description="programming with dickson"
		data-image="1.jpg"
		data-amount="<?php echo $n ?>"
		data-currency="usd"
		data-email="programmingwithdickson@gmail.com"

		>
			
		</script>
	</form>

</body>
</html>