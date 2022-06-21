    <style>
      .og-contents {
        margin-top: 60px;
        margin-left: 1em;
        margin-right: 1em;
        margin-bottom: 60px;
        width: calc( 100% - 2.2em );
      }
      .nav-right {
        position: absolute;
        top: 0;
        right: 0;
        margin-right: 2em;
        margin-top: 0.6em;
      }
    </style>
    <nav class="navbar navbar-inverse navbar-fixed-top">
      <div class="navbar-header">
        <a class="navbar-brand" style="color: white; font-weight: bold;" href="index.php">Ref<span style="color: #33ccff;">TKD</span></a>
      </div>
      <div id="navbar">
        <ul class="nav navbar-nav mr-auto">
<?php if( $user->is_auth()): ?>
          <li><a href="sankey.php">Network Flow (Sankey) Diagram</a></li>
          <li><a href="data.php">Referee Breakdown by Levels</a></li>
          <li><a href="admin.php">Manage Referee Records</a></li>
          <li><a href="map.php">Find Referees Near a Venue</a></li>
          <li><a href="about.php">About RefTKD</a></li>
<?php endif; ?>
          <li><a href="about.php">About</a></li>
        </ul>
<?php if( $user->is_auth()): ?>
        <div class="nav-right">
          <div style="display: inline-block; color: white; margin-right: 1.5em;"><span class="fas fa-user"></span> <?= $user->name ?></div>
          <div style="display: inline-block; color: gold; margin-right: 1.5em"><?= $user->role_icon() ?></div>
          <a class="btn btn-sm btn-primary" id="btn-logout"><span class="fas fa-right-from-bracket"></span> Logout</a>
        </div>
<?php endif; ?>
      </div>
    </nav>
    <script>
$( '#btn-logout' ).off( 'click' ).click( async ( ev ) => {
  await $.post( '/security/auth.php?logout', {} )
  .then( async( response ) => {
    window.location = 'index.php?message=<?= urlencode( base64_encode( "See you later {$user->fname}")) ?>';
  })
  .catch( async( error ) => {
    await Swal.fire( 'Logout failed', 'Contact the system owner or development team', 'error' );
  });
});
    </script>
