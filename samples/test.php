<?php

/**
 * @author Jeffro
 * @copyright 2011
 */

include('class/security.class.php');
include('class/database.class.php');
include('class/hosts.class.php');
include('class/pleskapi.class.php');
include('class/utilities.class.php');

$security = new Security();
$hosts = new Hosts();


if($security->sslactive()){
    /*
    if(!$hosts->hostexists("192.168.1.60")){
        if($hosts->addhost("192.168.1.60", 8443, "enterprise/control/agent.php", "admin", $security->encode("*******"), "1.3.1.0")){
            echo "Host succesvol aan database toegevoegd.";
        }
    }else{
        echo "Host bestaat al in database~";
        $host = $hosts->gethost("192.168.1.60");
        echo $security->decode($host['host_pass']); 
        if($hosts->delhost("192.168.1.60")){
            echo "Host is verwijderd";
        }else{
            echo "Of toch niet...";
        }
    }
    */
    $pleskapi = new PleskApi();
    $host = $hosts->gethost("192.168.1.60");
    
    //$params = array('pname' => 'Meneer de Uil (2314)', 'login' => 'mrdeuil', 'passwd' => 'Vuileonderbroek123');
    //if($pleskapi->createClient($host, $params)){
    //    echo "Aanmaken van client is gelukt! <br />";
    //}
    $clientinfo = $pleskapi->getClientInfo($host, "mrdeuil");
    $params = array('passwd' => 'testwachtwoord', 'cname' => 'Uilenproducties');
    try{
        if($pleskapi->setClient($host, $params, $clientinfo)){
            echo "Yippieeee";
        }
    }
    catch(Exception $ex){
        echo $ex->GetMessage();
    }
    /*    
    $clientinfo = $pleskapi->getClientInfo($host, "mrdeuil");
    echo $clientinfo;
    echo "Hallo Meneer de Uil, uw wachtwoord is " . $clientinfo->data->gen_info->password . "<br /> <br />";
    try{
        if($pleskapi->clientAddIpPool($host, $clientinfo)){
            echo "Ip is toegevoegd ofzo";
        }
    }
    */
    
    
}else{
    echo "HEY GTFO OF MY SYSTEM, SSL IS REQUIRED HERE CAPICHE?";
}
?>