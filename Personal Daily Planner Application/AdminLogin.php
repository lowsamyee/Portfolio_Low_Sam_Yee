<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>Daily Planner</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		
		<link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
		
		<link href="themes/css/flexslider.css" rel="stylesheet"/>
		<link href="themes/css/ADMINmain.css" rel="stylesheet"/>
	</head>
    <body>		
		<div id="top-bar" class="container">
			<div class="row">
				<div class="span4">
				
				</div>
				<div class="span8">
					<div class="account pull-right">
						<ul class="user-menu">				
						
						</ul>
					</div>
				</div>
			</div>
		</div>
		<div id="wrapper" class="container">
			<section class="navbar main-menu">
				<div class="navbar-inner main-menu">				
				
					<nav id="menu" class="pull-right">
						<ul>						
							
						</ul>
					</nav>
				</div>
			</section>			
			<section class="header_text sub">
			<img class="pageBanner" src="themes/images/pageBanner.png" alt="New products" >
				<h4><span>Admin Login</span></h4>
			</section>			
			<section class="main-content">				
				<div class="row">
					<div class="span5">					
						<h4 class="title"><span class="text"><strong>Login</strong> Form</span></h4>
						<form action="AdminLogin_db.php" method="post">
							<input type="hidden" name="next" value="/">
							<fieldset>
								<?php if (isset($_GET['error'])) { ?>
						     		<p class="error"><?php echo $_GET['error']; ?></p>
						     	<?php } ?>

								<div class="control-group">
									<label class="control-label">Username</label>
									<div class="controls">
										<input type="text" placeholder="Enter your username" id="username" name ="username"  class="input-xlarge">
									</div>
								</div>
								<div class="control-group">
									<label class="control-label">Password</label>
									<div class="controls">
										<input type="password" placeholder="Enter your password" id="password" name ="password" class="input-xlarge">
									</div>
								</div>
								<div class="control-group">
									<input tabindex="3" class="btn btn-inverse large" type="submit" value="Sign in">
									<hr>
									
								</div>
							</fieldset>
						</form>				
					</div>
									
				</div>
			</section>		
			<section id="footer-bar">
					
		</div>
		<script src="themes/js/common.js"></script>
		<script>
			$(document).ready(function() {
				$('#checkout').click(function (e) {
					document.location.href = "checkout.html";
				})
			});
		</script>		
    </body>
</html>