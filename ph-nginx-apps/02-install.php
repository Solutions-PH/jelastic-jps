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
	
		foreach($sites["certificates"] as $site) {
	
			echo "Start : ".$site["domain"]."\n";
			/*
			if(array_key_exists("repo", $site)) {
				if(!array_key_exists($site["repo"]["name"], $myRepos)) {
					echo "Repository creation : ".$site["repo"]["name"]."\n";
					
					$repos = $jelastic->addRepo([
						"appid" => "dashboard",
						"session" => $sessionAdmin['session'],
						"name" => $site["repo"]["name"],
						"url" => $site["repo"]["url"],
						"keyId" => 506
					]);
									
				}
			}
			*/
			
			echo "Nginx configuration"."\n";
			
			$command = "mkdir -p /var/www/webroot/".$site["path"]." && cd /var/www/webroot/".$site["path"]." && cd /etc/nginx/conf.d/sites-enabled/ && php -r \"copy('https://raw.githubusercontent.com/Solutions-PH/jelastic-jps/main/ph-nginx-apps/nginx/template.conf', '".$site["domain"].".conf');\" && mkdir -p /etc/nginx/conf.d/sites-enabled-ssl && cd /etc/nginx/conf.d/sites-enabled-ssl/ && php -r \"copy('https://raw.githubusercontent.com/Solutions-PH/jelastic-jps/main/ph-nginx-apps/nginx/template.ssl.conf', '".$site["domain"].".conf');\"";
			
			echo $command;
			
			$commands = [
				[
					"command" => $command,
					"params" => ""
				],[
					"command" => "sed -i 's/#server_name#/".$site["domain"]."/g' /etc/nginx/conf.d/sites-enabled/".$site["domain"].".conf",
					"params" => ""
				],[
					"command" => "sed -i 's/#server_alt_name#/".$site["domain"]."/g' /etc/nginx/conf.d/sites-enabled/".$site["domain"].".conf",
					"params" => ""
				],[
					"command" => "sed -i 's/#server_path#/".$site["path"]."/g' /etc/nginx/conf.d/sites-enabled/".$site["domain"].".conf",
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
			
			echo "Deploy: ".$site["site"]."\n";
			/*
			$repos = $jelastic->deploy([
				"envName" => $envName,
				"session" => $sessionAdmin['session'],
				"repo" => '{"url":"'.$site["repo"]["url"].'", "branch":"main","keyId":506}',
				"context" => $site["repo"]["name"],
				"nodeGroup" => "cp",
				"settings" => '{"autoResolveConflict": "true", "autoUpdate": "true", "autoUpdateInterval": "1"}'
			]);
			*/
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