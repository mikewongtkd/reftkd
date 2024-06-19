        <ul class="nav navbar-nav mr-auto">
<?php if( $user->is_auth()): ?>
          <li class="nav-item dropdown"><a href="sankey.php">Network Flow (Sankey) Diagram</a></li>
          <li><a href="data.php">Referee Breakdown by Levels</a></li>
          <li><a href="admin.php">Manage Referee Records</a></li>
          <li><a href="map.php">Find Referees Near a Venue</a></li>
<?php endif; ?>
          <li><a href="about.php">About</a></li>
        </ul>

