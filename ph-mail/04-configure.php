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
		
	$appid = $env["env"]["appid"];		
	
	echo "AppId = ".$appid."\n";

	$commands = [
		/*[
			"command" => "mysql -u root -pmyPassword -e \"USE vmail; INSERT INTO alias (address, domain, active) VALUES ('oct@lotibox.net', 'lotibox.net', 1); INSERT INTO forwardings (address, forwarding, domain, dest_domain, is_forwarding, active) VALUES ('oct@lotibox.net', 'oct@localhost', 'lotibox.net', 'localhost', 1, 1);\"",
			"params" => ""
		],*/[
			"command" => "if grep -Fxq 'oct' /etc/postfix/aliases;
			then
				echo 'ok';
			else
				echo 'oct: \"|/home/oct/script.php\"' >>  /etc/postfix/aliases;  
			fi",
			"params" => ""
		],[
			"command" => "mkdir -f /home/oct && cd /home/oct && php -r \"copy('https://raw.githubusercontent.com/Solutions-PH/jelastic-jps/main/".$envName."/script.php', 'script.php');\"",
		]
	];
	
	$cmd = $jelastic->execCmd([
		"envName" => $envName,
		"session" => $sessionAdmin['session'],
		"nodeGroup" => "vps",
		"commandList" => json_encode($commands),
	]);

	print_r($cmd);
	exit;	
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