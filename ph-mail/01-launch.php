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

	echo "Ready : http://".$env["env"]["domain"]."\n";
	echo "IP : ".$env["nodes"][0]["extIPs"][0]."\n";
	
}

?>