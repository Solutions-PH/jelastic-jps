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

	echo "Execute commands"."\n";
	
	$commands = [
		[
			"command" => "cd /var/www && php -r \"copy('https://github.com/acmephp/acmephp/releases/download/2.0.0/acmephp.phar', 'acmephp.phar');\" && php -r \"copy('https://github.com/acmephp/acmephp/releases/download/2.0.0/acmephp.phar.pubkey', 'acmephp.phar.pubkey');\"",
			"params" => ""
		],[
			"command" => "cd /var/www && php -r \"copy('https://raw.githubusercontent.com/Solutions-PH/jelastic-jps/main/".$envName."/config.yaml', 'config.yaml');\" && php acmephp.phar run config.yaml",
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