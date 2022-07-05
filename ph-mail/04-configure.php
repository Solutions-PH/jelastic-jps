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
			"command" => "if grep -Fq 'oct' /etc/postfix/aliases
			then
				echo 'ok';
			else
				echo 'oct: \"|/home/oct/script.php\"' >>  /etc/postfix/aliases;  
			fi",
			"params" => ""
		],[
			"command" => "mkdir -p /home/oct && mkdir -p /home/oct/mail && chmod 777 /home/oct/mail && cd /home/oct && php -r \"copy('https://raw.githubusercontent.com/Solutions-PH/jelastic-jps/main/".$envName."/script.php', 'script.php');\" && chmod 777 /home/oct/script.php",
			"params" => ""
		],[
			"command" => "rm -f /etc/postfix/master.cf && cd /etc/postfix && php -r \"copy('https://raw.githubusercontent.com/Solutions-PH/jelastic-jps/main/".$envName."/iredmail/master.cf', 'master.cf');\"",
			"params" => ""
		],[
			"command" => "amavisd-new genrsa /var/lib/dkim/lotibox.net.pem 1024",
			"params" => ""
		],[
			"command" => "newaliases && postfix reload",
			"params" => ""
		]
	];
	
	$cmd = $jelastic->execCmd([
		"envName" => $envName,
		"session" => $sessionAdmin['session'],
		"nodeGroup" => "vps",
		"commandList" => json_encode($commands),
	]);
	
	$commands = [
		[
			"command" => "amavisd-new showkeys",
			"params" => ""
		]
	];
	
	$cmd = $jelastic->execCmd([
		"envName" => $envName,
		"session" => $sessionAdmin['session'],
		"nodeGroup" => "vps",
		"commandList" => json_encode($commands),
	]);
	
	print_R($cmd);
	
	echo "Creér un champ SPF TXT @ avec v=spf1 mx ~all"."\n";
	echo "Creér un champ DKIM TXT dkim.domainkey et la clé ci-dessus"."\n";
	echo "Créer un champ DMARC TXT _dmarc avec v=DMARC1; p=none; pct=100; rua=mailto:dmarc@lotibox.net"."\n";
	echo "Vérifier avec : amavisd-new testkeys"."\n";
	
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