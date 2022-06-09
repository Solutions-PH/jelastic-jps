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
		
	if($env["result"]==11) {

		echo "Instance creation "."\n";
			
		$paramsRegAccount = array(
			'appid' => $jelastic->JcaAppId,
			'session' => $sessionAdmin['session'],
			'jps' => 'https://raw.githubusercontent.com/Solutions-PH/jelastic-jps/main/'.$envName.'/manifest.jps',
			'envName' => $envName,
			'displayName' => $displayName,
			'region' => 'thor'
		);
		
		$createInstance = $jelastic->marketPlaceJpsInstall($paramsRegAccount);
		
		$appid = $createInstance["appid"];
	
		$env = $jelastic->getEnvInfo(
			[
				'envName' => $envName,
				'session' => $sessionAdmin['session']
			]
		);
				
	} else {
		$appid = $env["env"]["appid"];		
	}
		
	echo "AppId = ".$appid."\n";

	echo "Execute commands"."\n";
	/*
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
	*/
	
	$cmd = "amavisd-new genrsa /var/lib/dkim/".$domain.".pem 1024 && chown amavis:amavis /var/lib/dkim/".$domain.".pem && chmod 0400 /var/lib/dkim/".$domain.".pem";

	$commands = [
		[
			"command" => "rm -f /etc/ssl/certs/iRedMail.crt && chmod 777 /root/.acmephp/master/certs/".$domain."/public/fullchain.pem && cp /root/.acmephp/master/certs/".$domain."/public/fullchain.pem /etc/ssl/certs/iRedMail.crt && chmod 600 /root/.acmephp/master/certs/".$domain."/public/fullchain.pem && rm -f /etc/ssl/private/iRedMail.key && chmod 777 /root/.acmephp/master/certs/".$domain."/private/key.private.pem && cp /root/.acmephp/master/certs/".$domain."/private/key.private.pem /etc/ssl/private/iRedMail.key && chmod 600 /root/.acmephp/master/certs/".$domain."/private/key.private.pem",
			"params" => ""
		],[
			"command" => $cmd,
		]
	];
		
	$cmd = $jelastic->execCmd([
		"envName" => $envName,
		"session" => $sessionAdmin['session'],
		"nodeGroup" => "vps",
		"commandList" => json_encode($commands),
	]);
	
	print_r($cmd);
	
	echo "Ask key with "."\n";
	echo "amavisd-new showkeys ".$domain."\n";
	
	$env = $jelastic->getEnvInfo(
		[
			'envName' => $envName,
			'session' => $sessionAdmin['session']
		]
	);

	echo "Restart : ".$envName."\n";
	
	foreach($env["nodes"] as $node) {
		
		$cmd = $jelastic->restart([
			"envName" => $envName,
			"nodeId" => $node["id"],
			"session" => $sessionAdmin['session']
		]);
					
	}
	
	echo "Ready : http://".$env["env"]["domain"]."\n";
	echo "IP : ".$env["nodes"][0]["extIPs"][0]."\n";
	
}

?>