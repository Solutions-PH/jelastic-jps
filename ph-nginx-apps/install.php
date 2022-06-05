<?php

$envName = "test-nginx";
$displayName = "displayName";

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
			'jps' => 'https://raw.githubusercontent.com/Solutions-PH/jelastic-jps/main/ph-nginx-apps/manifest.jps',
			'envName' => $envName,
			'displayName' => $displayName,
			'region' => 'thor'
		);
		
		$createInstance = $jelastic->marketPlaceJpsInstall($paramsRegAccount);
		
		$appid = $createInstance->appid;
	
	} else {
		$appid = $env["env"]["appid"];		
	}
				
	echo "AppId = ".$appid."\n";
	/*
	$repos = $jelastic->getRepos([
		'session' => $sessionAdmin['session'],
		"appid" => "dashboard"
	]);

	$myRepos = [];
	foreach($repos["array"] as $r) {
		$myRepos[$r["name"]] = $r;
	}

	echo "Repository detected = ".count($myRepos)."\n";
	*/
	
	echo "Execute commands"."\n";

	$commands = [
		/*
		[
			"command" => "rm -f /etc/nginx/conf.d/nossl.conf && rm -f /etc/nginx/conf.d/ssl.conf && rm -f /etc/nginx/conf.d/virtual.conf && echo 'include /etc/nginx/conf.d/sites-enabled/*.*;' > /etc/nginx/conf.d/sites.conf",
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
		],*/
		[
			"command" => "cd /var/www/webroot && php -r \"copy('https://github.com/acmephp/acmephp/releases/download/1.0.1/acmephp.phar', 'acmephp.phar');\" && php -r \"copy('https://github.com/acmephp/acmephp/releases/download/1.0.1/acmephp.phar.pubkey', 'acmephp.phar.pubkey');\" && php acmephp.phar --version",
			"params" => ""
		]
	];
		
	$cmd = $jelastic->execCmd([
		"envName" => $envName,
		"session" => $sessionAdmin['session'],
		"nodeGroup" => "cp",
		"commandList" => json_encode($commands),
	]);
	exit;
	$sites = yaml_parse(file_get_contents('./sites.yaml'));

	foreach($sites as $site) {

		echo "Start : ".$site["name"]."\n";
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
		
		echo "Deploy: ".$site["name"]."\n";
		
		$repos = $jelastic->deploy([
			"envName" => $envName,
			"session" => $sessionAdmin['session'],
			"repo" => '{"url":"'.$site["repo"]["url"].'", "branch":"main","keyId":506}',
			"context" => $site["repo"]["name"],
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
		echo "End : ".$site["name"]."\n";
		echo "---------\n";

		exit;
	}

	exit;
	
	print_r($sites);
	
	if(isset($createAccount['object']['activationKey']))
	{
		$paramsEnableAccount = array(
			'appid' => $jelastic->signUpAppId,
			'session' => $sessionAdmin['session'],
			'script' => 'activate',
			'activationKey' => $createAccount['object']['activationKey'],
			'password' => $password,
			'group' => $jelastic->group,
			'key' => $createAccount['object']['activationKey']
		);
		$enableAccount = $jelastic->enableAccount($paramsEnableAccount);
		if($enableAccount) {
			$paramsGetUid = array(
				'appid' => $jelastic->JcaAppId,
				'session' => $sessionAdmin['session'],
				'login' => $email
			);
			$getUid = $jelastic->getUid($paramsGetUid);
			if(isset($getUid['uid'])) {
				echo "Create account success.<br>Email : ".$email."<br>Password : ".$password."<br>Uid : ".$getUid['uid'];
			} else {
				echo "Get UID account failed.";
			}
		} else {
			echo "Enable account failed.";
		}
	}
	else
	{
		echo "Create account failed.";
	}
}
else
{
	echo "Login as admin failed.";
}

?>