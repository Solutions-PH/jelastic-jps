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
			'jps' => 'https://raw.githubusercontent.com/Solutions-PH/jelastic-jps/main/ph-nginx-apps/manifest.jps',
			'envName' => $envName,
			'displayName' => $displayName,
			'region' => 'thor'
		);
		
		$createInstance = $jelastic->marketPlaceJpsInstall($paramsRegAccount);
		
		print_R($createInstance);
		
		$appid = $createInstance->appid;
	
	} else {
		$appid = $env["env"]["appid"];		
	}
				
	echo "AppId = ".$appid."\n";
	
	echo "Execute commands"."\n";

	$commands = [
		[
			"command" => "rm -f /etc/nginx/conf.d/sites-enabled/default.conf && rm -f /etc/nginx/conf.d/nossl.conf && rm -f /etc/nginx/conf.d/ssl.conf && rm -f /etc/nginx/conf.d/virtual.conf && echo 'include /etc/nginx/conf.d/sites-enabled/*.*;' > /etc/nginx/conf.d/sites.conf",
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
		],
		[
			"command" => "cd /var/www/webroot && php -r \"copy('https://github.com/acmephp/acmephp/releases/download/2.0.0/acmephp.phar', 'acmephp.phar');\" && php -r \"copy('https://github.com/acmephp/acmephp/releases/download/2.0.0/acmephp.phar.pubkey', 'acmephp.phar.pubkey');\" && php acmephp.phar --version",
			"params" => ""
		],[
			"command" => "cd /var/www/webroot && php -r \"copy('https://raw.githubusercontent.com/Solutions-PH/jelastic-jps/main/ph-nginx-apps/config.yaml', 'config.yaml');\" && php acmephp.phar run config.yaml" ,
			"params" => ""
		]
	];
		
	$cmd = $jelastic->execCmd([
		"envName" => $envName,
		"session" => $sessionAdmin['session'],
		"nodeGroup" => "cp",
		"commandList" => json_encode($commands),
	]);

}

?>