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
	
		$sites = yaml_parse(file_get_contents('./config.yaml'));
		
		echo "Domain configuration"."\n";
		
		foreach($sites["certificates"] as $site) {
		
			echo "Start : ".$site["domain"]."\n";		
			
			echo "Nginx configuration"."\n";
			
			$command = "cd /etc/nginx/conf.d/sites-enabled-ssl && php -r \"copy('https://raw.githubusercontent.com/Solutions-PH/jelastic-jps/main/ph-api/nginx/template.ssl.conf', '".$site["domain"].".conf');\"";
					
			$commands = [
				[
					"command" => $command,
					"params" => ""
				],[
					"command" => "sed -i 's/#server_name#/".$site["domain"]."/g' /etc/nginx/conf.d/sites-enabled-ssl/".$site["domain"].".conf",
					"params" => ""
				],[
					"command" => "sed -i 's/#server_path#/".$site["distinguished_name"]["organization_unit_name"]."/g' /etc/nginx/conf.d/sites-enabled-ssl/".$site["domain"].".conf",
					"params" => ""
				]
			];
			
			if(array_key_exists("subject_alternative_names", $site)) {
				$commands[] = [
					"command" => "sed -i 's/#server_alt_name#/".$site["subject_alternative_names"]."/g' /etc/nginx/conf.d/sites-enabled-ssl/".$site["domain"].".conf",
					"params" => ""
				];
			} else {
				$commands[] = [
					"command" => "sed -i 's/#server_alt_name#/ /g' /etc/nginx/conf.d/sites-enabled-ssl/".$site["domain"].".conf",
					"params" => ""
				];
			}
			
			$cmd = $jelastic->execCmd([
				"envName" => $envName,
				"session" => $sessionAdmin['session'],
				"nodeGroup" => "cp",
				"commandList" => json_encode($commands),
			]);
	
			echo "End : ".$site["domain"]."\n";
			echo "---------\n";
			
		}
		

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