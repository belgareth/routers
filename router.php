<?php

$username = 'admin';
$password = 'admin';
$ip = '192.168.1.1';
 
// code below
$ip = "http://{$ip}/";
 
$auth_enc =  base64_encode($username.':'.$password);
 
date_default_timezone_set('Australia/Perth');
$cookies = dirname(__FILE__).'/cookies-tplink.txt';
 
//delete the existing cookie file, important
unlink($cookies);
 
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookies);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookies);
curl_setopt($ch, CURLOPT_COOKIE, 'Authorization=Basic '.$auth_enc.';path=/');
curl_setopt($ch, CURLOPT_REFERER, $ip);
curl_setopt($ch, CURLOPT_URL, $ip);
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
$output = curl_exec($ch);
 
//grab the connection information
curl_setopt($ch, CURLOPT_URL, $ip.'info.html');
$output = curl_exec($ch);
 
preg_match_all('%<td class=\\\\"dataStyle\\\\">(\d*?)</td>%im', $output, $result, PREG_PATTERN_ORDER);
$Upstream = $result[1][0];
$Downstream = $result[1][1];
var_dump($Upstream);
var_dump($Downstream);
 
//grab session key from reboot router page
curl_setopt($ch, CURLOPT_URL, $ip.'resetrouter.html');
$output = curl_exec($ch);
 
if (preg_match('/var sessionKey=\'(.*?)\';/im', $output, $regs)) {
    $sessionKey = $regs[1];
}
var_dump($sessionKey);
 
//reboot the modem
if ($sessionKey && false) {
    curl_setopt($ch, CURLOPT_URL, $ip.'rebootinfo.cgi?sessionKey='.$sessionKey);
    $output = curl_exec($ch);
    echo "Modem has been rebooted\r\n";
}
 
curl_close($ch);

?>