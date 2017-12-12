<?php 

function run($url,$header=false,$body=false){
	$url = $url;
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_HEADER,true);
	if($body){ #post
		curl_setopt($ch, CURLOPT_POSTFIELDS,$body);
	}
	if(!$header){
		$header = array();
	}
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	$response = curl_exec($ch);
	curl_close($ch);
	return $response;
}



function init($username,$password,$ip){
	$indexUrl = "http://{$ip}/html/home.html";
	$loginUrl = "http://{$ip}/api/user/login";
	$rebootUrl = "http://{$ip}/api/device/control";
	#get session Id
	echo "\n-------Sesssion------\n";

	$responseSession = run($indexUrl);
	preg_match('/SessionID=/', $responseSession, $matches);
	preg_match( '#<meta name="csrf_token" content="([^"]+)"\/>#siU',$responseSession, $tags);
	$token = $tags[1];
	print_r($matches);
	echo("Token:".$token."\n");
	if (!empty($matches[0]))
	{
     	$cookie = strstr($responseSession, 'SessionID=');
     	//print("1. SessionId:{$cookie}\n");
     	$cookie = strstr($cookie, ';', true);
     	//print("2. SessionId:{$cookie}\n");
     	$cookie = ltrim($cookie, 'SessionID=');
     	//print("3. SessionId:{$cookie}\n");
	}
	$sessionId = $cookie;
	print("SessionId:{$sessionId}\n");

	#login to get token
	echo "\n-------Login------\n";

	$encodedPass = base64_encode(hash('sha256',($username.base64_encode(hash('sha256',($password))).$token)));
	$headersLogin = [
    		'Cookie: SessionID='.$sessionId,
    		"Origin: http://{$ip}",
    		'Accept-Encoding: gzip,deflate',
    		'Accept-Language: en-US,en;q=0.8',
    		'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/37.0.2062.120 Chrome/37.0.2062.120 Safari/537.36',
    		'X-Requested-With: XMLHttpRequest',
    		'Content-Type: application/x-www-form-urlencoded; charset=utf-8',
    		'Accept: */*',
    		'__RequestVerificationToken: '.$token,
    		"Referer: http://{$ip}/html/home.html", //Your referrer address
    		'Connection: keep-alive',
    		"Host: {$ip}"
	];
	$responseLogin = run($loginUrl,$headersLogin,"<?xml version='1.0' encoding='UTF-8'?><request><Username>{$username}</Username><Password>	{$encodedPass}</Password><password_type>4</password_type></request>");
	print_r($responseLogin);


	#reboot

 	echo "\n-------Reboot------\n";
 
	preg_match('#__RequestVerificationTokenone:(.+)\n#siU',$responseLogin,$matches);
	preg_match( '#<meta name="csrf_token" content="([^"]+)"\/>#siU',$responseSession, $tags);
	print_r($matches);
	$newtoken = trim($matches[1]);
	preg_match('#Set-Cookie:SessionID=(.+);path#siU',$responseLogin,$matches);
	print_r($matches);
	$newsessionId = trim($matches[1]);
	echo("\nlogin Token(To be used to reboot router):".$newtoken."\n");
	echo("\nlogin Session(To be used to reboot router):".$newsessionId."\n");
	$headers = [
    		"Origin: http://{$ip}",
    		'Accept-Encoding: gzip,deflate',
    		'Accept-Language: en-US,en;q=0.8',
    		'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/37.0.2062.120 Chrome/37.0.2062.120 Safari/537.36',
    		'X-Requested-With: XMLHttpRequest',
    		'Content-Type: application/x-www-form-urlencoded; charset=utf-8',
    		'Accept: */*',
    		"Referer: http://{$ip}/html/reboot.html", //Your referrer address
    		'__RequestVerificationToken: '.$newtoken,
    		'Cookie: SessionID='.$newsessionId,
    		'Connection: keep-alive'
	];
	$responseReboot = run($rebootUrl,$headers,"<?xml version='1.0' encoding='UTF-8'?><request><Control>1</Control></request>");
	print_r($responseReboot);
	echo("\n Rebooting {$ip} In Progess...Check Router In 5 minutes \n");
}

#init
$ip = $argv[1];
$username = $argv[2];
$password = $argv[3];
init($username,$password,$ip);
?>
