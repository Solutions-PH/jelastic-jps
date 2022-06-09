#!/usr/bin/php
<?php

$fd = fopen("php://stdin", "r");
$email = "";
while (!feof($fd)) {
	$line = fread($fd, 1024);
	$email .= $line;
}
fclose($fd);

ob_start();

echo $email;

$info = ob_get_contents();
ob_end_clean();

$fp = fopen("/home/oct/mail/mail_".$dj->format('Y-m-d H:i:s u').".html", "w+");
fwrite($fp, $info);
fclose($fp);

exit(0);

?>