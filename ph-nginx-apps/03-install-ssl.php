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
			
			$command = "cd /etc/nginx/conf.d/sites-enabled-ssl && php -r \"copy('https://raw.githubusercontent.com/Solutions-PH/jelastic-jps/main/ph-nginx-apps/nginx/template.ssl.conf', '".$site["domain"].".conf');\"";
					
			$commands = [
				[
					"command" => $command,
					"params" => ""
				],[
					"command" => "sed -i 's/#server_name#/".$site["domain"]."/g' /etc/nginx/conf.d/sites-enabled-ssl/".$site["domain"].".conf",
					"params" => ""
				],[
					"command" => "sed -i 's/#server_alt_name#/".$site["domain"]."/g' /etc/nginx/conf.d/sites-enabled-ssl/".$site["domain"].".conf",
					"params" => ""
				],[
					"command" => "sed -i 's/#server_path#/".$site["path"]."/g' /etc/nginx/conf.d/sites-enabled-ssl/".$site["domain"].".conf",
					"params" => ""
				]
			];
			
			$cmd = $jelastic->execCmd([
				"envName" => $envName,
				"session" => $sessionAdmin['session'],
				"nodeGroup" => "cp",
				"commandList" => json_encode($commands),
			]);
			
			echo "Deploy: ".$site["domain"]."\n";
			
			$repos = $jelastic->deploy([
				"envName" => $envName,
				"session" => $sessionAdmin['session'],
				"repo" => '{"url":"'.$site["repo"].'", "branch":"main","keyId":506}',
				"context" => $site["path"],
				"nodeGroup" => "cp",
				"settings" => '{"autoResolveConflict": "true", "autoUpdate": "true", "autoUpdateInterval": "1"}'
			]);
			
			print_R($repos);
			
			/*
			$commads = [
				[
					"command" => "cd /var/www/webroot/ && wget https://raw.githubusercontent.com/Solutions-PH/jelastic-jps/main/nginx/scripts/install.php && php install.php",
					"params" => ""
				]
			];
			
			$repos = $jelastic->execCmd([
				"envName" => $envName,
				"session" => $sessionAdmin['session'],
				"nodeGroup" => "cp",
				"commandList" => json_encode($commands),
			]);
			*/
			echo "End : ".$site["site"]."\n";
			echo "---------\n";
		
			exit;
		
		}
		

	}
}
?>