<?php

$f = fopen("/var/www/webroot/ROOT/index.php", "w");
fwrite($f, "OK<?php phpinfo();?>");
fclose($f);