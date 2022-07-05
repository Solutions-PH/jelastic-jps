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
					
		echo "AppId = ".$appid."\n";
	
		echo "Nginx configuration"."\n";
		
		$command = "cd /etc/nginx/sites-enabled && php -r \"copy('https://raw.githubusercontent.com/Solutions-PH/jelastic-jps/main/ph-metabase/nginx/default.ssl.conf', 'default.ssl.conf');\"";
				
		$commands = [
			[
				"command" => $command,
				"params" => ""
			]
		];
			
		$cmd = $jelastic->execCmd([
			"envName" => $envName,
			"session" => $sessionAdmin['session'],
			"nodeGroup" => "vps",
			"commandList" => json_encode($commands),
		]);

		echo "End : bi.7.fr\n";
		echo "---------\n";
				

	}

	echo "Restart : ".$envName."\n";
	
	foreach($env["nodes"] as $node) {
		
		$cmd = $jelastic->restart([
			"envName" => $envName,
			"nodeId" => $node["id"],
			"session" => $sessionAdmin['session']
		]);
					
	}
	
}
?>