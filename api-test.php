<?php 

//http://www.theyworkforyou.com/api/getConstituency?postcode=cf24+4lf&output=php

//EYCTEZEiStQfEp83c6GjvrNr

// Include the API binding
require_once 'inc/twfyapi.php';

// Set up a new instance of the API binding
$twfyapi = new TWFYAPI('EYCTEZEiStQfEp83c6GjvrNr');

// Get the constituency from TWFY from the postcode in the query string
$postcode = $_GET['postcode'];

$constituency = $twfyapi->query('getConstituency', array('output' => 'php', 'postcode' => $postcode));
$constituency = unserialize($constituency);

//Get the MP from the Guardian's politics API
$constit_url = 'http://www.theguardian.com/politics/api/constituency/' . $constituency['guardian_id'] . '/json';
 
//Get the output and decode it into a PHP object
$json_output = file_get_contents($constit_url);
$constituency_obj = json_decode($json_output);
// $mpinfo = objectToArray($constituency_obj);

//Get the MP url
$mp_url = $constituency_obj->constituency->mp->{'json-url'};

//Get the Political Person object from the Guardian's politics API
//Get the output and decode it into a PHP object
$json_output = file_get_contents($mp_url);
$mp_obj = json_decode($json_output);

// Print out the list
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en-US">
<head profile="http://gmpg.org/xfn/11">
<meta charset="utf-8" />
</head>

<?php 
// echo '<pre>';
//   print_r($mp_obj);
// echo '</pre>';

echo 'MP: ' . $mp_obj->person->name . '<br/>';
echo 'MP email: ' . $mp_obj->person->{'contact-details'}->{'email-addresses'}[0]->email . '<br/>';
$website = $mp_obj->person->{'contact-details'}->{'websites'}[0]->url;
echo 'MP website: <a href="' . $website . '">' . $website . '</a><br/>';

if(isset($mp_obj->person->image)){
	$image = $mp_obj->person->image;
}
else{
	//give us a mystery man
	$image = 'http://blogtimenow.com/images/creating-custom-default-gravatar-wordpress.jpg';
}


echo 'MP image: <img src="' . $image . '">';

?>


</html>