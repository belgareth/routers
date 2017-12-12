<?php

// Get data from the open HTTP stream
function fromhttp ($fp) {
 $text = fgets($fp,4096);
 return $text;
}

// Send data to the open HTTP stream
function tohttp ($fp,$text) {
 fwrite($fp,$text);
}

// Assign user and password
$user = "admin";
$pass = "admin";

// Set server IP (DSL router IP)
$server = "192.168.1.1";

// URL to request
$url = "/basic_setup_finish.html?Save+and+Restart=Save+and+Restart";

// Create the Authorization header
$auth = "Authorization: Basic " .
 base64_encode($user . ":" . $pass);

$getrequest = <<<HTTP
GET $url HTTP/1.1
Accept: text/plain,text/html
Host: localhost
User-Agent: PHP
Connection: Close
$auth


HTTP;
// Note the two blank lines above; due to PHP heredoc idiosyncrasies,
// this creates one blank line in the data to be sent

// If we can open a connection (stream) to the router
if ($fp = fsockopen($server, 80, $errno, $errstr, 30)) {

 // Set our timeout appropriately
 stream_set_timeout ($fp,2);

 // Send the request to the HTTP stream
 tohttp($fp,$getrequest);

 // Get and print response from HTTP stream
 do {
 $temp = fromhttp($fp);
 print $temp;
 } while (!empty($temp));

 // Close the connection
 fclose($fp);

} else {

 // Report connection failure
 print "Could not open connection to router!";

}

?>