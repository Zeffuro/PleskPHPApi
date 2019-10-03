<?php

/**
 * @author Jeffro
 * @copyright 2011
 */

require('init.php');

$security = new Security();

if($security->sslactive()){
    ?>
    <!-- Menutje -->
    <center>
        <a href="index.php?act=addhost">Voeg server toe</a> | <a href="index.php?act=addcustomer">Voeg klant toe</a> | <a href="index.php?act=addorder">Voeg order toe</a>
        <table>
            <tr>
                <form action="index.php?act=input&orderid=" method="get">
                        <input type="hidden" name="act" value="input">
                        <td>Ordernummer: <input type="text" size="6" name="orderid" id="orderid" value="<?=$_GET['orderid']?>"></td>
                        <td><input type="submit" value="Verwerken"></td>
                </form>
                
                <form action="index.php?act=editdel" method="post">
                    <td>
                        <select name="host">
                            <?php
                            try{
                                $hosts = new Hosts();
                                $hostlist = $hosts->gethosts();
                                foreach($hostlist as $hostinfo){
                                    ?>
                                    <option value="<?= $hostinfo['host_address'] ?>"><?= $hostinfo['host_address'] ?></option>
                                    <?php
                                }
                            }
                            catch (Exception $ex)
                            {
                                echo $ex->GetMessage();
                            }
                            ?>
                        </select>
                        <input type="submit" value="Aanpassen" name="edit">
                        <input type="submit" value="Verwijder" name="delete">
                    </td>
                </tr>  
            </form>
        </table>
    </center>
    <br />
    <?php
    
    if(isset($_GET['act']) AND $_GET['act'] == 'addhost'){
        include('view/addhost.php');
    }
    
    if(isset($_GET['act']) AND $_GET['act'] == 'addcustomer'){
        include('view/addcustomer.php');
    }
    
    if(isset($_GET['act']) AND $_GET['act'] == 'doaddcustomer'){
        if($_POST['pasw'] != $_POST['pasw_confirm']){
            die('Wachtwoorden komen niet overeen');
        }
        
        $params = array($_POST['klantid'], $_POST['eig_voorletters'], $_POST['eig_tussenvoegsel'], $_POST['eig_naam'], $_POST['clogin'], $_POST['pasw']);
        
        $customers = new Customers();
        if($customers->createCustomer($params)){
            echo "Klant succesvol aangemaakt.";
        }
    }
     
    if(isset($_GET['act']) AND $_GET['act'] == 'doaddorder'){
        $params = array($_POST['orderid'], $_POST['domein'], $_POST['ext'], $_POST['ip'], $_POST['pakket'], $_POST['klantnummer']);
        
        $orders = new Orders();
        if($orders->createOrder($params)){
            echo "Order succesvol aangemaakt.";
        }
    }
    
    if(isset($_GET['act']) AND $_GET['act'] == 'addorder'){
        include('view/addorder.php');
    }
    
    if(isset($_GET['act']) AND $_GET['act'] == 'editdel'){
        if(isset($_POST['edit'])){
            include('view/edithost.php');
        }
        if(isset($_POST['delete'])){
            include('view/confirmdel.php');
        }
    }
    
    if(isset($_GET['act']) AND $_GET['act'] == 'input'){
        try{
            $orderid = $_GET['orderid'];
            $orders = new Orders();
            $orderinfo = $orders->getOrder($orderid);
            $customers = new Customers();
            $customerinfo = $customers->getCustomer($orderinfo['klantnummer']);
            
            //Generate Plesk Client Name and make this client in Plesk if it doesn't exist.
            $clientname = $customerinfo['eig_voorletters'] . " ";
            if($customerinfo['eig_tussenvoegsel'] != ""){
                $clientname = $clientname . $customerinfo['eig_tussenvoegsel'] . " ";
            }
            $clientname = $clientname . $customerinfo['eig_naam'] . " (" . $customerinfo['klantid'] . ")";
            
            $hosts = new Hosts();
            $host = $hosts->gethost($orderinfo['ip']);
            $client = $customerinfo['clogin'];
            
            $gen_info = array('cname' => $customerinfo['eig_bedrijfsnaam'], 'pname' => $clientname, 'login' => $client, 'passwd' => $customerinfo['pasw']);
            $permissions = array('manage_drweb' => 'true', 'manage_spamfilter' => 'true', 'manage_maillists' => 'true', 'make_dumps' => 'true', 'dashboard' => 'false');
            $params = array('gen_info' => $gen_info, 'permissions' => $permissions);
            $pleskapi = new PleskApi();
            
            if(!$pleskapi->clientExists($host, $client)){
                if($pleskapi->createClient($host, $params)) {
                    echo "Client bestaat nog niet, begin aanmaak client. <br />";
                }  
                $clientinfo = $pleskapi->getClientInfo($host, $client);
                
                //Check if host has Plesk version of 9 or higher for the overuse limit
                if(version_compare($host['host_version'], '1.6.0.0') >= 0){
                    echo "Plesk Versie is 9 of hoger, stel overuse limit in. <br />";
                    $pleskapi->setClientOveruseLimit($host, $clientinfo);
                }
                echo "Voeg IP toe aan client IP pool. <br />";
                $pleskapi->clientAddIpPool($host, $clientinfo);
            }else{
                $clientinfo = $pleskapi->getClientInfo($host, $client);
                echo "Client bestaat al, ga verder met domein code. <br />";
            }
            
            //Start creating domain.
            $domain = $orderinfo['domein'] . "." . $orderinfo['ext'];
            
            if((strlen($orderinfo['domein'])) > 16) 
            {
                $admlogin = substr($orderinfo['domein'], 0, 16);
            }else{
                $admlogin = $orderinfo['domein'];
            }

            $ftpuser = preg_replace('/^[0-9]/','',$admlogin);
            
            if($orderinfo['pakket'] == 'forward'){
                //If customer has a forward pack
                if($orderinfo['fortype'] == 'frame'){
                    //Forwardtype frame forward
                   $htype = 'frm_fwd'; 
                   $hosting = array('dest_url' => $orderinfo['fwdurl'], 'ip_address' => $orderinfo['ip']);
                }else if($orderinfo['fortype'] == 'standaard'){
                    //Forwardtype standard forward
                    $htype = 'std_fwd';
                    $hosting = array('dest_url' => $orderinfo['fwdurl'], 'ip_address' => $orderinfo['ip']);
                }else if ($orderinfo['fortype'] == 'ip'){
                    $htype = 'none';
                    //Stuff for making DNS records coming later.
                }
                $action = "1"; 
            }else if ($orderinfo['pakket'] == 'koppel'){
                $alias = $domain;
                $domain = $orderinfo['fwdurl'];
                $action = "2";
            }else{
                //If customer has physical hosting
                $templates = new HostingTemplates();
                $template = $templates->getTemplate($orderinfo['pakket']);
                
                //Calculate limits
                $diskspace_limit = $template['opslag'] * 5242880;
                $diskspace_limit = round($diskspace_limit);
                $mailbox_limit = $template['mailbox'];
                $traffic_limit = ($template['dataverkeer'] + $orderinfo['dataverkeer']) * 1073741824;
                $mysql_limit = $template['mysql'];
                $mailboxquota = $diskspace_limit * 0.5;
                if($orderinfo['cgi'] != "1"){
                    if($template['php'] == 1){
                        $scripting = "true";
                    }else{
                        $scripting = "false";
                    }
                }else{
                    $scripting = "true";
                }
                
                $htype = 'vrt_hst';
                $hosting = array('ftp_login' => $ftpuser, 'ftp_password' => $customerinfo['pasw'], 'php' => $scripting, 'webstat' => 'webalizer', 'webstat_protected' => 'true', 'errdocs' => 'true', 'ip_address' => $orderinfo['ip']);
                $action = "1";
            }

            //Check what action will be triggered.
            if($action == "1"){
                $gen_setup = array('client_id' => $clientinfo->id, 'name' => $domain, 'htype' => $htype, 'ip_address' => $orderinfo['ip'], 'status' => '0');
                $params = array('gen_setup' => $gen_setup, 'hosting' => $hosting); //, 'limits' => $limits);
                
                
                
                if(!$pleskapi->domainExists($host, $domain)){
                    echo "Domein bestaat nog niet, start aanmaak domein. <br />";
                    if($pleskapi->createDomain($host, $params)){
                        echo "Domein is aangemaakt. <br />";
                    }
                    
                    $domaininfo = $pleskapi->getDomainInfo($host, $domain);
                    if(version_compare($host['host_version'], '1.6.0.0') >= 0){
                        echo "Plesk Versie is 9 of hoger, stel overuse limit in voor domein. <br />";
                        $pleskapi->setDomainOveruseLimit($host, $domaininfo);
                    }
                    $params = array('name' => $orderinfo['domein'], 'mailbox:enabled' => 'true', 'alias' => 'info', 'password' => $customerinfo['pasw'], 'permissions:manage_drweb' => 'true', 'permissions:manage_spamfilter' => 'true');
                    
                    if($orderinfo['pakket'] != "forward"){
                        if($pleskapi->createEmail($host, $domaininfo, $params)){
                            echo "Mailbox met alias aangemaakt.  <br />";
                        }
                    }
                }else{
                    $domaininfo = $pleskapi->getDomainInfo($host, $domain);
                    echo "Domein bestaat al, ga verder met limits instellen.  <br />";
                }
                
                if($orderinfo['pakket'] == 'forward'){
                    //If customer has a forward pack
                    $params = array('hosting' => $hosting);
                    
                }else{
                    //If customer has physical hosting
                    
                    $limits = array('mbox_quota' => $mailboxquota, 'max_box' => $mailbox_limit, 'max_db' => $mysql_limit, 'max_traffic' => $traffic_limit, 'disk_space' => $diskspace_limit);
                    $prefs = array('www' => 'true', 'stat_ttl' => '6');
                    $params = array('limits' => $limits, 'prefs' => $prefs);
                }            
                
                if($pleskapi->setDomain($host, $domaininfo, $params)){
                    echo "Limits zijn ingesteld.  <br />";
                }
                
                if($scripting == "true"){
                    if(version_compare($host['host_version'], '1.5.0.0') >= 0){
                        $params = array('ip_address' => $orderinfo['ip'], 'scripting' => $scripting);
                        if($pleskapi->setDomainScripting($host, $domaininfo, $params)){
                            echo "Scripting ingesteld op domein";
                        }
                    }
                }else{
                    if(version_compare($host['host_version'], '1.5.0.0') >= 0){
                        $params = array('ip_address' => $orderinfo['ip'], 'scripting' => $scripting);
                        if($pleskapi->setDomainScripting($host, $domaininfo, $params)){
                            echo "Scripting ingesteld op domein";
                        }
                    }
                }
            }else if($action == "2"){
                if(!$pleskapi->domainExists($host, $alias)){
                    echo "Domeinalias bestaat nog niet, start aanmaak domein. <br />";
                    if($pleskapi->domainExists($host, $domain)){
                        $domaininfo = $pleskapi->getDomainInfo($host, $domain);
                        if($pleskapi->createDomainAlias($host, $domaininfo, $alias)){
                            echo "Domeinalias is aangemaakt. <br />";
                        }
                    }
                }
            }
        }
        catch (Exception $ex)
        {
            echo $ex->GetMessage();
        }
        
    }
    
    if(isset($_GET['act']) AND $_GET['act'] == 'doedithost'){
        $host_id = $_POST['host_id'];
        $host = $_POST['host_address'];
        $ip = $_POST['host_ipaddress'];
        $port = $_POST['host_port'];
        $path = $_POST['host_path'];
        $version = $_POST['host_version'];
        if(empty($host) OR empty($ip) OR empty($port) OR empty($version)){
            die('n00b, je hebt 1 van de velden leeg gelaten!');
        }
        
        if (!preg_match(
            '/^(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)(?:[.](?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)){3}$/',
        $ip)) { 
            die('Je hebt een ongeldig ip ingevoerd.');
        }
        
        if(!is_numeric($port)){
            die('Je hebt een ongeldige port ingevoerd!');
        }
        
        
        
        try
        {
            $hosts = new Hosts();
            $hostinfo = $hosts->gethostbyid($host_id);
            
            $temphost = array("host_address" => $host, 
                    "host_ipaddress" => $ip, 
                    "host_port" => $port, 
                    "host_path" => $path, 
                    "host_user" => $hostinfo['host_user'], 
                    "host_pass" => $hostinfo['host_pass'], 
                    "host_authmethod" => $hostinfo['host_authmethod'],
                    "host_key" => $hostinfo['host_key'], 
                    "host_version" => $version);
            $pleskapi = new PleskApi();
            
            if($pleskapi->testConnection($temphost) == false){
                die('Kan geen verbinding maken/authorizeren bij host. Er is iets fout.');
            }
            
            $params = array($host, $ip, $port, $path, $version);
            
            if($hosts->edithost($host_id, $params)){
                echo "Host is aangepast.";
                header("Refresh: 3; URL=index.php");
            }
            
        }
        
        catch (Exception $ex)
        {
            echo $ex->GetMessage();
        }
          
    }
    
    if(isset($_GET['act']) AND $_GET['act'] == 'dodelhost'){
        if(isset($_POST['confirm'])){
            try{
                $hosts = new Hosts();
                if($hosts->delhost($_POST['confirm'])){
                    echo "Host succesvol verwijderd.";
                    header("Refresh: 3; URL=index.php");
                }
            }
            catch (Exception $ex)
            {
                echo $ex->GetMessage();
            }
        }else{
            die('Confirm was niet aangevinkt.');
        }
    }
    
    if(isset($_GET['act']) AND $_GET['act'] == 'doaddhost'){
        $host = $_POST['host_address'];
        $ip = $_POST['host_ipaddress'];
        $port = $_POST['host_port'];
        $path = $_POST['host_path'];
        $user = $_POST['host_user'];
        $pass = $_POST['host_pass'];
        $pass_confirm = $_POST['host_pass_confirm'];
        $authmethod = $_POST['host_authmethod'];
        $version = $_POST['host_version'];

        if($pass != $pass_confirm){
            die('Hoe krijg jij het voor elkaar om hetzelfde wachtwoord voor de 2e keer verkeerd te plakken? :S');
        }
        
        if($authmethod != "1"){
            $authmethod = "0";
        }
        
        $pass = $security->encode($pass);
        
        if(empty($host) OR empty($ip) OR empty($port) OR empty($user) OR empty($pass) OR empty($version)){
            die('n00b, je hebt 1 van de velden leeg gelaten!');
        }
        
        if (!preg_match(
            '/^(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)(?:[.](?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)){3}$/',
        $ip)) { 
            die('Je hebt een ongeldig ip ingevoerd.');
        }
        
        if(!is_numeric($port)){
            die('Je hebt een ongeldige port ingevoerd!');
        }
        
        
        
        $hosts = new Hosts();
        try
        {
            if($hosts->hostexists($host)){
                die('Deze host staat al in de database.');
            }
            
            $temphost = array("host_address" => $host, 
                    "host_ipaddress" => $ip, 
                    "host_port" => $port, 
                    "host_path" => $path, 
                    "host_user" => $user, 
                    "host_pass" => $pass, 
                    "host_authmethod" => "0", 
                    "host_version" => $version);
            $pleskapi = new PleskApi();
            
            if($pleskapi->testConnection($temphost) == false){
                die('Kan geen verbinding maken/authorizeren bij host. Er is iets fout.');
            }
            
            if($authmethod == "1"){
                
                $key = $pleskapi->createSecretKey($temphost, WEBSRVIP);
                $key = $security->encode($key);
                $user = "";
                $pass = "";
            }else{
                $key = "";
            }
            
            $params = array($host, $ip, $port, $path, $user, $pass, $key, $authmethod, $version);
            
            if($hosts->addhost($params)){
                echo "Host is toegevoegd";
                header("Refresh: 3; URL=index.php");
            }
        
        }
        catch (Exception $ex)
        {
            echo $ex->GetMessage();
        }
        
    }
    
}else{
    die('Om gebruik te kunnen maken van dit systeem dien je
            gebruik te maken van een beveiligde verbinding.');
}
?>