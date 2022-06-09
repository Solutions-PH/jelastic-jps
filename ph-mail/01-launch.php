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
	echo "ok";
	exit;
	
	$sites = yaml_parse(file_get_contents('./config.yaml'));

	echo "Domain configuration"."\n";

	echo "---------\n";
	
	foreach($sites["certificates"] as $site) {
	
		echo "Start : ".$site["domain"]."\n";		
		
		echo "Nginx configuration"."\n";
		
		$path = str_replace("/var/www/webroot/", "", $site["solver"]["root"]);
		$path = str_replace("/public", "", $path);
		
		$command = "mkdir -p /var/www/webroot/".$path." && cd /var/www/webroot/".$path." && cd /etc/nginx/conf.d/sites-enabled/ && php -r \"copy('https://raw.githubusercontent.com/Solutions-PH/jelastic-jps/main/$envName/nginx/template.conf', '".$site["domain"].".conf');\"";
				
		$publicPath = str_replace("/", "\/", $path."/public");

		$commands = [
			[
				"command" => $command,
				"params" => ""
			],[
				"command" => "sed -i 's/#server_name#/".$site["domain"]."/g' /etc/nginx/conf.d/sites-enabled/".$site["domain"].".conf",
				"params" => ""
			],[
				"command" => "sed -i 's/#server_path#/".$publicPath."/g' /etc/nginx/conf.d/sites-enabled/".$site["domain"].".conf",
				"params" => ""
			]
		];
		
		if(array_key_exists("subject_alternative_names", $site)) {
			$commands[] = [
				"command" => "sed -i 's/#server_alt_name#/".$site["subject_alternative_names"]."/g' /etc/nginx/conf.d/sites-enabled/".$site["domain"].".conf",
				"params" => ""
			];
		} else {
			$commands[] = [
				"command" => "sed -i 's/#server_alt_name#/ /g' /etc/nginx/conf.d/sites-enabled/".$site["domain"].".conf",
				"params" => ""
			];
		}
		
		$cmd = $jelastic->execCmd([
			"envName" => $envName,
			"session" => $sessionAdmin['session'],
			"nodeGroup" => "cp",
			"commandList" => json_encode($commands),
		]);

		if(!array_key_exists($path, $contexts)) {
			$repos = $jelastic->deploy([
				"envName" => $envName,
				"session" => $sessionAdmin['session'],
				"repo" => '{"url":"'.$site["distinguished_name"]["locality"].'", "branch":"main","keyId":'.$keyId.'}',
				"context" => $path,
				"nodeGroup" => "cp",
				"settings" => '{"autoResolveConflict": "true", "autoUpdate": "true", "autoUpdateInterval": "1"}'
			]);
			
		}
		
		echo "End : ".$site["domain"]."\n";
		echo "---------\n";
		
	}

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