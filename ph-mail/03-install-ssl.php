<?php

/* require config file */
require "./00-config.php";

/* require jelastic class file */
require "./vendor/Jelastic.php";

/* call Jelastic class */
$jelastic = new Jelastic();

echo "Login"."\n";

$paramsSessionAdmin = array(
  'appid' => $jelastic->JcaAppId,
  'email' => $jelastic->apiUsername,
  'password' => $jelastic->apiPassword
);

$sessionAdmin = $jelastic->login($paramsSessionAdmin);

echo "Session = ".$sessionAdmin['session']."\n";

if(isset($sessionAdmin['session']))
{

	echo "Instance check "."\n";

	$env = $jelastic->getEnvInfo(
		[
			'envName' => $envName,
			'session' => $sessionAdmin['session']
		]
	);
		
	$appid = $env["env"]["appid"];		
	
	echo "AppId = ".$appid."\n";

	$cmd = "amavisd-new genrsa /var/lib/dkim/".$domain.".pem 1024 && chown amavis:amavis /var/lib/dkim/".$domain.".pem && chmod 0400 /var/lib/dkim/".$domain.".pem";

	$commands = [
		[
			"command" => "rm -f /etc/ssl/certs/iRedMail.crt && chmod 777 /root/.acmephp/master/certs/".$domain."/public/fullchain.pem && cp /root/.acmephp/master/certs/".$domain."/public/fullchain.pem /etc/ssl/certs/iRedMail.crt && chmod 600 /root/.acmephp/master/certs/".$domain."/public/fullchain.pem && rm -f /etc/ssl/private/iRedMail.key && chmod 777 /root/.acmephp/master/certs/".$domain."/private/key.private.pem && cp /root/.acmephp/master/certs/".$domain."/private/key.private.pem /etc/ssl/private/iRedMail.key && chmod 600 /root/.acmephp/master/certs/".$domain."/private/key.private.pem",
			"params" => ""
		],[
			"command" => "",
		]
	];
	
	$cmd = $jelastic->execCmd([
		"envName" => $envName,
		"session" => $sessionAdmin['session'],
		"nodeGroup" => "vps",
		"commandList" => json_encode($commands),
	]);

	print_r($cmd);
	
}

?>