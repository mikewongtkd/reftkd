<?php include_once( 'security.php' ); ?>
<html>
	<head>
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/css/bootstrap.min.css" integrity="sha384-HSMxcRTRxnN+Bdg0JdbxYKrThecOKuH5zCYotlSAcp1+c8xmyTe9GYg1l9a69psu" crossorigin="anonymous">
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/css/bootstrap-theme.min.css" integrity="sha384-6pzBo3FDv/PJ8r2KRkGHifhEocL+1X2rVCTTkUfGk7/0pbek5mMa1upzvWbrUbOZ" crossorigin="anonymous">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" integrity="sha512-KfkfwYDsLkIlwQp6LFnl8zNdLGxu9YAA1QvwINks4PhcElQSvqcyVLLD9aMhXd13uQjoXtEKNosOWaZqXgel0g==" crossorigin="anonymous" referrerpolicy="no-referrer" />
		<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.17/dist/sweetalert2.min.css" rel="stylesheet" />
		<link href="/include/js/toast.css" rel="stylesheet" />
		<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/js/bootstrap.min.js" integrity="sha384-aJ21OjlMXNL5UyIl/XNwTMqvzeRMZH2w8c5cRVpzpU8Y5bApTppSuUkhZXN0VxHd" crossorigin="anonymous"></script>
		<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.17/dist/sweetalert2.all.min.js"></script>
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
		<title>RefTKD - Taekwondo Referee Development Management System</title>
		<style>
		.title-card {
			position: absolute;
			left: 50%;
			top: 15%;
			background: rgba( 255, 255, 255, 0.925 );
			border: 1px solid #666;
			border-radius: 12px;
			padding: 0 12px 12px 12px;
			width: 400px;
			transform: translateX( -50% );
			text-align: center;
		}

		.title-card .subtitle {
			color: #666;
		}

		body { overflow-y: hidden; background-color: #ebebeb; }
		img { position: absolute; top: 40px; left: 0; right: 0; width: 100%; }

		#login, #btn-actions {
			width: 320px;
			position: absolute;
			left: 50%;
			top: 70%;
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
		
		#img-org-logo {
			position: absolute;
			top: 120px;
			left: 60px;
			width: 200px;
		}

		#img-ref-logo {
			position: absolute;
			top: calc( 90% - 120px );
			left: 90%;
			width: 120px;
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
	<img src="/images/backgrounds/<?= $image ?>" />
	<img src="/images/cuta-logo.png" id="img-org-logo">
	<img src="/images/referee-white-text.png" id="img-ref-logo">
	<div class="title-card">
		<h1><span class="text-primary">RefTKD</span></h1>
		<p class="subtitle">Taekwondo Referee Development Management System</p>
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
		<div class="form-group" id="id-group">
			<label for="id">Enter Your User ID Below</label>
			<input class="form-control" type="text" name="id" id="id">
		</div>
		<div class="form-group" id="password-group">
			<label for="password">Enter Your Password Below</label>
			<input class="form-control" type="password" name="password" id="password">
			<a class="btn btn-lg btn-primary text-light" role="button" id="btn-login" style="width: 240px; margin-top: 20px">Login</a>
		</div>
	</form>
<?php endif; ?>
</div>
<?php include( 'components/footer.php' ); ?>
<script>
$( '#btn-login' ).off( 'click' ).click( ev => {
	let message = {
		id : $( '#id' ).val(),
		password : $( '#password' ).val(),
	};
	let encoded = encodeURI( btoa( `Welcome ${message.id.toUpperCase()}` ));
	$.post( 'security/auth.php?login', message )
	.then( async( response ) => {
		window.location = `index.php?message=${encoded}`;
	})
	.catch( async( error ) => {
		await Swal.fire( 'Login failed', 'The ID and password do not match our records. Please try again.', 'error' )
		.then( response => { location.reload(); });
	});
});
</script>
	</body>
</html>
