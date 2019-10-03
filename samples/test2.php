<?php

/**
 * @author Jeffro
 * @copyright 2011
 */

require('init.php');

$security = new Security();

if($security->sslactive()){
    try
    {
        $hosts = new Hosts();
        $host = $hosts->getHost("192.168.1.60");
        $pleskapi = new PleskApi();
        $client = $pleskapi->getClientInfo($host, 'janbham');
        $domain = $pleskapi->getDomainInfo($host, "lol.nl");
        #$gen_setup = array('name' => 'koekjesdoos.nl', 'ip_address' => '192.168.1.60', 'status' => '0');
#        $hosting = array('ftp_login' => 'test', 'ftp_password' => 'ikbendik123', 'ip_address' => '192.168.1.60');
#        $limits = array('max_box' => '3', 'max_db' => '0', 'max_traffic' => '5368709120', 'disk_space' => '52428800');
#        $params = array('gen_setup' => $gen_setup); //, 'hosting' => $hosting, 'limits' => $limits
#        
#        $gen_info = array('pname' => 'Jan Boterham (1366)', 'login' => 'janbham', 'passwd' => 'honingkanlekkerzijn');
#        
#        /*if($pleskapi->setDomain($host, $domain, $params)){
#            echo "Domein is aangemaakt.";
#        }*/
#        $permissions = array('manage_subdomains' => 'true', 'manage_dns' => 'true');
#        $params = array('gen_info' => $gen_info, 'permissions' => $permissions);
#        if($pleskapi->setClient($host, $client, $params)) {
#            echo "Client aangemaakt";
#        }
    //$pleskapi->createDomainAlias($host, $domain, 'lol.eu');
    $params = array('name' => 'kouters', 'mailbox:enabled' => 'true', 'mailbox:quota' => '200000000', 'alias' => 'arno', 'password' => 'geheim', 'permissions:manage_drweb' => 'true');
    $pleskapi->createEmail($host, $domain, $params);
    }
    catch (Exception $ex)
    {
        echo $ex->GetMessage();
    }
}else{
    ?>
    SSL needs to be active
    <?php
}

?>