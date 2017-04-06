<?php
//require_once 'app/Mage.php';
//Mage::app();
//$host = "127.0.0.1/6ar_02/index.php"; //our online shop url
//$source = new SoapClient("http://" . $host . "/api/v2_soap/?wsdl=1", array('trace' => 1, 'cache_wsdl' => WSDL_CACHE_NONE)); //soap handle
//$apiuser = "itcan"; //webservice user login
//$apikey = "itcan@soap";
//$sess = $source->login($apiuser, $apikey); //we do login
//echo $sess;
//try {
//    $result = $source->shoppingCartShippingList($sess, 'ksa_en');
//    print('<pre>');
//    print_r($result);
//} catch (SoapFault $f) {
//    print_r($f->getMessage());
//}
//

ini_set('soap.wsdl_cache_enabled', 0);
ini_set('soap.wsdl_cache_ttl', 900);
ini_set('default_socket_timeout', 15);


//$params = array('param1'=>$param1);


$wsdl = 'http://localhost/6ar/index.php/api/v2_soap?wsdl=1';

$options = array(
    'uri'=>'http://schemas.xmlsoap.org/soap/envelope/',
    'style'=>SOAP_RPC,
    'use'=>SOAP_ENCODED,
    'soap_version'=>SOAP_1_1,
    'cache_wsdl'=>WSDL_CACHE_NONE,
    'connection_timeout'=>15,
    'trace'=>true,
    'encoding'=>'UTF-8',
    'exceptions'=>true,
);
try {
    $soap = new SoapClient($wsdl, $options);
   // $data = $soap->method($params);
}
catch(Exception $e) {
    die($e->getMessage());
}

var_dump($data);
die;