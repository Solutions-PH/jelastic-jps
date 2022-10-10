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
		
	if($env["result"]==0) {
		$appid = $env["env"]["appid"];		
					
		$command = "cd /var/www/webroot && php -r \"copy('https://raw.githubusercontent.com/Solutions-PH/jelastic-jps/main/ph-api/config.yaml', 'config.yaml');\" && php acmephp.phar run config.yaml";
				
		$commands = [
			[
				"command" => "cd /var/www/webroot && php -r \"copy('https://github.com/acmephp/acmephp/releases/download/2.0.0/acmephp.phar', 'acmephp.phar');\" && php -r \"copy('https://github.com/acmephp/acmephp/releases/download/2.0.0/acmephp.phar.pubkey', 'acmephp.phar.pubkey');\"",
				"params" => ""
			],
			[
				"command" => $command,
				"params" => ""
			]
		];
		
		$cmd = $jelastic->execCmd([
			"envName" => $envName,
			"session" => $sessionAdmin['session'],
			"nodeGroup" => "cp",
			"commandList" => json_encode($commands),
		]);
		
		print_r($cmd);

	}
}
?>