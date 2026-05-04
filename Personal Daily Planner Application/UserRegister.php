<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>Daily Planner</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		
		<link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
		
		<link href="themes/css/flexslider.css" rel="stylesheet"/>
		<link href="themes/css/main.css" rel="stylesheet"/>
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
				<h4><span>User Regsiter</span></h4>
			</section>			
			<section class="main-content">				
				
					<div class="span7">					
						<h4 class="title"><span class="text"><strong>Register</strong> Form</span></h4>

						<form action="UserRegister_db.php" method="post" class="form-stacked">
							<fieldset>
								<div class="control-group">
									<label class="control-label" for= "username">Username</label>
									<div class="controls">
										<input type="text" placeholder="Enter your username" id="username" name ="username" class="input-xlarge">
									</div>
								</div>
								
								<div class="control-group">
									<label class="control-label" for= "password">Password:</label>
									<div class="controls">
										<input type="password" placeholder="Enter your password" id="password"name ="password" class="input-xlarge">
									</div>
								</div>	
								<div class="control-group">
									<label class="control-label" for= "email">Email address:</label>
									<div class="controls">
										<input type="text" placeholder="Enter your email" id="email" name="email" class="input-xlarge">
									</div>
								</div>		
								<p class="reset">Already Register <a tabindex="4" href="UserLogin.php" title="Recover your username or password">Go to Login</a></p>
								
								<hr>
								<div class="actions"><input tabindex="9" class="btn btn-inverse large" type="submit" value="Create your account"></div>
							</fieldset>
						</form>					
					</div>				
				</div>
			</section>		
			<section id="footer-bar">
				<div class="row">
					<div class="span3">
						
					</div>
				
				</div>	
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