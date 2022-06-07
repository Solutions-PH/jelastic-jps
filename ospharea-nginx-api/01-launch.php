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

	$contexts = [];
	foreach($env["env"]["contexts"] as $context) {
		$contexts[$context["context"]] = $context["archivename"];
	}

	echo "Execute commands"."\n";

	$commands = [
		[
			"command" => "rm -f /etc/nginx/conf.d/sites-enabled/default.conf && rm -f /etc/nginx/conf.d/nossl.conf && rm -f /etc/nginx/conf.d/ssl.conf && rm -f /etc/nginx/conf.d/virtual.conf && echo 'include /etc/nginx/conf.d/sites-enabled/*.*;' > /etc/nginx/conf.d/sites.conf && mkdir -p /etc/nginx/conf.d/sites-enabled-ssl/ ",
			"params" => ""
		],[
			"command" => "if grep -Fxq 'extension=intl.so' /etc/php.ini
			then
				echo 'ok';
			else
				echo 'extension=intl.so' >> /etc/php.ini;
			fi",
			"params" => ""
		],[
			"command" => "if grep -Fxq 'extension=gd.so' /etc/php.ini
			then
				echo 'ok';
			else
				echo 'extension=gd.so' >> /etc/php.ini;
			fi",
			"params" => ""
		],[
			"command" => "if grep -Fxq 'extension=pdo.so' /etc/php.ini
			then
				echo 'ok';
			else
				echo 'extension=pdo.so' >> /etc/php.ini;
			fi",
			"params" => ""
		],[
			"command" => "if grep -Fxq 'extension=pdo_dblib.so' /etc/php.ini
			then
				echo 'ok';
			else
				echo 'extension=pdo_dblib.so' >> /etc/php.ini;
			fi",
			"params" => ""
		],[
			"command" => "if grep -Fxq 'extension=imagick.so' /etc/php.ini
			then
				echo 'ok';
			else
				echo 'extension=imagick.so' >> /etc/php.ini;
			fi",
			"params" => ""
		],[
			"command" => "if grep -Fxq 'extension=psr.so' /etc/php.ini
			then
				echo 'ok';
			else
				echo 'extension=psr.so' >> /etc/php.ini;
			fi",
			"params" => ""
		],[
			"command" => "if grep -Fxq 'extension=phalcon.so' /etc/php.ini
			then
				echo 'ok';
			else
				echo 'extension=phalcon.so' >> /etc/php.ini;
			fi",
			"params" => ""
		]
	];
		
	$cmd = $jelastic->execCmd([
		"envName" => $envName,
		"session" => $sessionAdmin['session'],
		"nodeGroup" => "cp",
		"commandList" => json_encode($commands),
	]);
	
	$sites = yaml_parse(file_get_contents('./config.yaml'));

	echo "Domain configuration"."\n";

	echo "---------\n";
	
	foreach($sites["certificates"] as $site) {
	
		echo "Start : ".$site["domain"]."\n";		
		
		echo "Nginx configuration"."\n";
		
		$path = str_replace("/var/www/webroot/", "", $site["solver"]["root"]);
		$path = str_replace("/public", "", $path);
		
		$command = "mkdir -p ".$path." && cd ".$path." && cd /etc/nginx/conf.d/sites-enabled/ && php -r \"copy('https://raw.githubusercontent.com/Solutions-PH/jelastic-jps/main/'.$envName.'/nginx/template.conf', '".$site["domain"].".conf');\"";
		
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
		echo $path;
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