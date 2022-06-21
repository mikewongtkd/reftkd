<?php include_once( 'security.php' ); ?>
<html>
	<head>
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/css/bootstrap.min.css" integrity="sha384-HSMxcRTRxnN+Bdg0JdbxYKrThecOKuH5zCYotlSAcp1+c8xmyTe9GYg1l9a69psu" crossorigin="anonymous">
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/css/bootstrap-theme.min.css" integrity="sha384-6pzBo3FDv/PJ8r2KRkGHifhEocL+1X2rVCTTkUfGk7/0pbek5mMa1upzvWbrUbOZ" crossorigin="anonymous">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" integrity="sha512-KfkfwYDsLkIlwQp6LFnl8zNdLGxu9YAA1QvwINks4PhcElQSvqcyVLLD9aMhXd13uQjoXtEKNosOWaZqXgel0g==" crossorigin="anonymous" referrerpolicy="no-referrer" />
		<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.17/dist/sweetalert2.min.css" rel="stylesheet" />
		<link rel="preconnect" href="https://fonts.googleapis.com">
		<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
		<link href="https://fonts.googleapis.com/css2?family=News+Cycle:wght@400;700&family=PT+Sans:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
		<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/js/bootstrap.min.js" integrity="sha384-aJ21OjlMXNL5UyIl/XNwTMqvzeRMZH2w8c5cRVpzpU8Y5bApTppSuUkhZXN0VxHd" crossorigin="anonymous"></script>
		<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.17/dist/sweetalert2.all.min.js"></script>
		<script src="include/js/toast.js"></script>
<?php
if( isset( $_GET[ 'error' ])):
	$error = base64_decode( urldecode( $_GET[ 'error' ]));
?>
	<script>
	$(() => {
		Toast.fire({ icon: 'error', title: '<?= $error ?>' })
	});
	</script>
<?php endif; ?>
<?php
if( isset( $_GET[ 'message' ])):
	$message = base64_decode( urldecode( $_GET[ 'message' ]));
?>
	<script>
	$(() => {
		Toast.fire({ icon: 'success', title: '<?= $message ?>' })
	});
	</script>
<?php endif; ?>
		<title>RefTKD - Taekwondo Referee Management System</title>
		<style>
		.title-card {
			position: absolute;
			left: 50%;
			top: 15%;
			background: rgba( 255, 255, 255, 0.925 );
			border: 1px solid #666;
			border-radius: 12px;
			padding: 0 12px 12px 12px;
			width: 360px;
			transform: translateX( -50% );
			text-align: center;
		}

		.title-card h1 {
			font-family: 'PT Sans';
			font-weight: 700;
		}

		.title-card .subtitle {
			font-family: 'News Cycle'; 
			font-size: 14pt;
			color: #666;
		}

		body { overflow-y: hidden; background-color: #ebebeb; }
		img { position: absolute; top: 40px; left: 0; right: 0; width: 100%; }

		#login, #btn-actions {
			width: 360px;
			position: absolute;
			left: 50%;
			bottom: 0;
			transform: translateX( -50% ) translateY( -30% );
			border-radius: 12px;
		}

		#login {
			background: rgba( 255, 255, 255, 0.925 );
			border-radius: 12px;
			border: 1px solid #666;
			padding-top: 12px;
			padding-left: 36px;
			padding-right: 36px;
			padding-bottom: 12px;
		}

		#btn-actions .btn-primary { border-color: #163c61; }
		#btn-login { width: 100%; margin-top: 24px; }
		
		#img-bg {
			position: absolute;
			top: 0;
			left: 0;
			width: 100%;
		}
		
		#img-org-logo {
			position: absolute;
			top: 120px;
			left: 60px;
			width: 200px;
		}

		#img-ref-logo {
			position: absolute;
			top: calc( 90% - 180px );
			left: 90%;
			width: 180px;
		}

		</style>
	</head>
	<body>
<?php include( 'components/header.php' ); ?>

<div class="og-contents">
<?php
	$images = array_values( array_filter( scandir( 'images/backgrounds' ), function ( $x ) { return preg_match( '/\.jpe?g/i', $x ); }));
	$n      = count( $images ) - 1;
	$i      = rand( 0, $n );
	$image  = $images[ $i ];
?>
	<img src="/images/backgrounds/<?= $image ?>" id="img-bg" />
	<img src="/images/cuta-logo.png" id="img-org-logo">
	<img src="/images/referee-white-text.png" id="img-ref-logo">
	<div class="title-card">
		<h1><span class="text-dark">Ref</span><span class="text-primary">TKD</span></h1>
		<p class="subtitle">Taekwondo Referee Management System</p>
	</div>
<?php if( $user->is_auth()): ?>
	<div class="btn-group-vertical" id="btn-actions">
		<a class="btn btn-lg btn-primary text-light" href="sankey.php">Network Flow (Sankey) Diagram</a>
		<a class="btn btn-lg btn-primary text-light" href="levels.php">Referee Breakdown by Levels</a>
		<a class="btn btn-lg btn-primary text-light" href="admin.php">Manage Referee Records</a>
		<a class="btn btn-lg btn-primary text-light" href="map.php">Find Referees Near a Venue</a>
		<a class="btn btn-lg btn-primary text-light" href="about.php">About RefTKD</a>
	</div>
<?php else: ?>
	<form id="login">
		<div class="form-group" id="email-group">
			<label for="email">Enter Your Email Below</label>
			<input class="form-control" type="email" name="email" id="email">
		</div>
		<div class="form-group" id="password-group">
			<label for="password">Enter Your Password Below</label>
			<input class="form-control" type="password" name="password" id="password">
			<a class="btn btn-lg btn-primary text-light" role="button" id="btn-login">Login</a>
		</div>
	</form>
<?php endif; ?>
</div>
<?php include( 'components/footer.php' ); ?>
<script>
$( '#btn-login' ).off( 'click' ).click( ev => {
	let message = {
		email : $( '#email' ).val(),
		password : $( '#password' ).val(),
	};
	$.post( 'security/auth.php?login', message )
	.then( async( response ) => {
		console.log( 'RESPONSE', response ); // MW
		let encoded = encodeURI( btoa( `Welcome ${response.fname}` ));
		window.location = `index.php?message=${encoded}`;
	})
	.catch( async( error ) => {
		await Swal.fire( 'Login failed', 'The ID and password do not match our records. Please try again.', 'error' )
		.then( response => { location.reload(); });
	});
});
$(() => {
	$( '#email' ).focus();
});
</script>
	</body>
</html>
