<?php

/**
 * @author Jeffro
 * @copyright 2011
 */
 
class PleskApi{
     
    private $ignoreerror = false;
    
    /**
     * PleskApi::curlInit()
     * 
     * @param mixed $host
     * @return
     */
    private function curlInit($host)
    {
        $security = new Security();
        if($host['host_authmethod'] == '0'){ 
            $password = $security->decode($host['host_pass']);
            $httpheader = array("HTTP_AUTH_LOGIN: {$host['host_user']}",
                                "HTTP_AUTH_PASSWD: {$password}", 
                                "HTTP_PRETTY_PRINT: TRUE",
                                "Content-Type: text/xml");
        }else{
            $key = $security->decode($host['host_key']);
            $httpheader = array("KEY: {$key}", 
                                "HTTP_PRETTY_PRINT: TRUE",
                                "Content-Type: text/xml");
        }
        $security = new Security();
        $password = $security->decode($host['host_pass']);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, "https://{$host['host_address']}:{$host['host_port']}/{$host['host_path']}");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST,           true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);    
        curl_setopt($curl, CURLOPT_HTTPHEADER, $httpheader);
        return $curl;
    }
    
    /**
     * PleskApi::sendRequest()
     * 
     * @param mixed $curl
     * @param mixed $packet
     * @return
     */
    private function sendRequest($curl, $packet){
        curl_setopt($curl, CURLOPT_POSTFIELDS, $packet);
        $result = curl_exec($curl);
        if (curl_errno($curl)) {
            $error  = curl_error($curl);
            $errorcode = curl_errno($curl);
            curl_close($curl);
            throw new Exception("Er is iets mis gegaan: <br /><br /> Foutcode: " . $errorcode . "<br /> Foutmelding: " . $error );
        }
        curl_close($curl);
        if(DEBUG == true){
            echo "<pre>";
            echo htmlentities($packet);
            echo "</pre><br /><pre>";
            echo htmlentities($result);
            echo "</pre>";
        }
        return $result;
    }
    
    /**
     * PleskApi::toggleIgnoreError()
     * 
     * @return
     */
    private function toggleIgnoreError(){
        if($this->ignoreerror == false){
            $this->ignoreerror = true;
        }else{
            $this->ignoreerror = false;
        }
    }
    
    /**
     * PleskApi::parseResponse()
     * 
     * @param mixed $response
     * @return
     */
    private function parseResponse($response){
        $xml = new SimpleXMLElement($response);
        $status = $xml->xpath('//status');
        if($status[0] == "error" AND $this->ignoreerror == false){
            $errorcode = $xml->xpath('//errcode');
            $errortext = $xml->xpath('//errtext');
            throw new Exception("Er is iets mis gegaan: <br /><br /> Foutcode: ". $errorcode[0] . "<br /> Foutmelding: " . $errortext[0]);
        }else{
            return $response;
        }
    }
    
    /**
     * PleskApi::getIpAddressesPacket()
     * 
     * @return
     */
    private function getIpAddressesPacket(){
        // Create new XML document.
        $xml = new DomDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;
        
        // Create packet
        $packet = $xml->createElement('packet');      
        $packet->setAttribute('version', '1.4.2.0');
        $xml->appendChild($packet);
        $ip = $xml->createElement('ip');
        $packet->appendChild($ip);
        $get = $xml->createElement('get');
        $ip->appendChild($get);
        return $xml->saveXML();
    }
    
    /**
     * PleskApi::getIpAddresses()
     * 
     * @param mixed $host
     * @return
     */
    public function getIpAddresses($host){
        $curl = $this->curlInit($host);
        $packet = $this->getIpAddressesPacket();
        $response = $this->sendRequest($curl, $packet);
        if($this->parseResponse($response)){
            $xml = new SimpleXMLElement($response);
            $result = $xml->ip->get->result;
            return $result;
        }
    }
    
    /**
     * PleskApi::setIpForwardingPacket()
     * 
     * @param mixed $host
     * @param mixed $params
     * @return
     */
    private function setIpForwardingPacket($host, $params){
        // Create new XML document.
        $xml = new DomDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;
        
        // Create packet
        $packet = $xml->createElement('packet');      
        $packet->setAttribute('version', '1.4.2.0');
        $xml->appendChild($packet);
        $dns = $xml->createElement('dns');
        $packet->appendChild($dns);
        $add_rec = $xml->createElement('add_rec');
        $dns->appendChild($add_rec);
        return $xml->saveXML();
    }
    
    /**
     * PleskApi::testConnection()
     * 
     * @param mixed $host
     * @return
     */
    public function testConnection($host){
        $result = $this->getIpAddresses($host);
        $status = $result->xpath('//status');
        if($status[0] == "ok"){
            return true;
        }else{
            return false;
        }
    }
    
    /**
     * PleskApi::getSecretKeysPacket()
     * 
     * @return
     */
    private function getSecretKeysPacket(){
        // Create new XML document.
        $xml = new DomDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;
        
        // Create packet
        $packet = $xml->createElement('packet');      
        $packet->setAttribute('version', '1.4.2.0');
        $xml->appendChild($packet);
        $secret_key = $xml->createElement('secret_key');
        $packet->appendChild($secret_key);
    }
    
    /**
     * PleskApi::createSecretKeyPacket()
     * 
     * @param mixed $ip
     * @return
     */
    private function createSecretKeyPacket($ip){
        // Create new XML document.
        $xml = new DomDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;
        
        // Create packet
        $packet = $xml->createElement('packet');      
        $packet->setAttribute('version', '1.4.2.0');
        $xml->appendChild($packet);
        $secret_key = $xml->createElement('secret_key');
        $packet->appendChild($secret_key);
        $create = $xml->createElement('create');
        $secret_key->appendChild($create);
        $ip_address = $xml->createElement('ip_address', $ip);
        $create->appendChild($ip_address);
        return $xml->saveXML();
    }
    
    /**
     * PleskApi::createSecretKey()
     * 
     * @param mixed $host
     * @param mixed $ip
     * @return
     */
    public function createSecretKey($host, $ip){
        $curl = $this->curlInit($host);
        $packet = $this->createSecretKeyPacket($ip);
        $response = $this->sendRequest($curl, $packet);
        if($this->parseResponse($response)){
            $xml = new SimpleXMLElement($response);
            $secretkey = $xml->xpath('//key');
            $key = (string) $secretkey[0];
            return $key;
        }
        
    }

    /**
     * PleskApi::clientInfoPacket()
     * 
     * @param mixed $client_login
     * @return
     */
    private function clientInfoPacket($client_login){
        // Create new XML document.
        $xml = new DomDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;
        
        // Create packet
        $packet = $xml->createElement('packet');      
        $packet->setAttribute('version', '1.4.2.0');
        $xml->appendChild($packet);
        $client = $xml->createElement('client');
        $packet->appendChild($client);
        $get = $xml->createElement('get');
        $client->appendChild($get);
        $filter = $xml->createElement('filter');
        $get->appendChild($filter);
        $login = $xml->createElement('login', $client_login);
        $filter->appendChild($login);
        $dataset = $xml->createElement('dataset');
        $get->appendChild($dataset);
        $gen_info = $xml->createElement('gen_info', 'true');
        $dataset->appendChild($gen_info);
        return $xml->saveXML();
    }
    
    /**
     * PleskApi::getClientInfo()
     * 
     * @param mixed $host
     * @param mixed $login
     * @return
     */
    public function getClientInfo($host, $login){
        $curl = $this->curlInit($host);
        $packet = $this->clientInfoPacket($login);
        $response = $this->sendRequest($curl, $packet);
        if($this->parseResponse($response)){
            $xml = new SimpleXMLElement($response);
            $result = $xml->client->get->result;
            return $result;
        }
    }

    /**
     * PleskApi::clientExists()
     * 
     * @param mixed $host
     * @param mixed $login
     * @return
     */
    public function clientExists($host, $login){
        $this->toggleIgnoreError();
        $result = $this->getClientInfo($host, $login);
        $status = $result->xpath('//status');
        if($status[0] == "error"){
            $this->toggleIgnoreError();
            return false;
        }else{
            $this->toggleIgnoreError();
            return true;
        }
    }

    /**
     * PleskApi::domainInfoPacket()
     * 
     * @param mixed $domain
     * @return
     */
    private function domainInfoPacket($domain){
        // Create new XML document.
        $xml = new DomDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;
        
        // Create packet
        $packet = $xml->createElement('packet');
        $packet->setAttribute('version', '1.4.2.0');
        $xml->appendChild($packet);
        $client = $xml->createElement('domain');
        $packet->appendChild($client);
        $get = $xml->createElement('get');
        $client->appendChild($get);
        $filter = $xml->createElement('filter');
        $get->appendChild($filter);
        $login = $xml->createElement('domain_name', $domain);
        $filter->appendChild($login);
        $dataset = $xml->createElement('dataset');
        $get->appendChild($dataset);
        $gen_info = $xml->createElement('gen_info');
        $dataset->appendChild($gen_info);
        return $xml->saveXML();
    }

    /**
     * PleskApi::getDomainInfo()
     * 
     * @param mixed $host
     * @param mixed $domain
     * @return
     */
    public function getDomainInfo($host, $domain){
        $curl = $this->curlInit($host);
        $packet = $this->domainInfoPacket($domain);
        $response = $this->sendRequest($curl, $packet);
        if($this->parseResponse($response)){
            $xml = new SimpleXMLElement($response);
            $result = $xml->domain->get->result;
            return $result;
        }
        
    }

    /**
     * PleskApi::domainExists()
     * 
     * @param mixed $host
     * @param mixed $domain
     * @return
     */
    public function domainExists($host, $domain){
        $this->toggleIgnoreError();
        $result = $this->getDomainInfo($host, $domain);
        $status = $result->xpath('//status');
        if($status[0] == "error"){
            $this->toggleIgnoreError();
            return false;
        }else{
            $this->toggleIgnoreError();
            return true;
        }
    }

    /**
     * PleskApi::clientCreatePacket()
     * 
     * @param mixed $params
     * @return
     */
    private function clientCreatePacket($params){
        // Create new XML document.
        $xml = new DomDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;
        
        // Create packet
        $packet = $xml->createElement('packet');
        $packet->setAttribute('version', '1.4.2.0');
        $xml->appendChild($packet);
        $client = $xml->createElement('client');
        $packet->appendChild($client);
        $add = $xml->createElement('add');
        $client->appendChild($add);
        foreach($params as $name => $pvalues) { 
            $node = $xml->createElement($name); 
            $add->appendChild($node); 
            foreach ($pvalues as $key => $value) { 
                $xmlelement = $xml->createElement($key, $value); 
                $node->appendChild($xmlelement); 
            } 
        }
        return $xml->saveXML();
    }

    /**
     * PleskApi::createClient()
     * 
     * @param mixed $host
     * @param mixed $params
     * @return
     */
    public function createClient($host, $params){
        $curl = $this->curlInit($host);
        $packet = $this->clientCreatePacket($params);
        $response = $this->sendRequest($curl, $packet);
        if($this->parseResponse($response)){
            return true;
        }
    }
    
    /**
     * PleskApi::clientSetOveruseLimitPacket()
     * 
     * @param mixed $clientinfo
     * @return
     */
    private function clientSetOveruseLimitPacket($clientinfo){
        // Create new XML document.
        $xml = new DomDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;
        
        // Create packet
        $packet = $xml->createElement('packet');
        $packet->setAttribute('version', '1.6.0.0');
        $xml->appendChild($packet);
        $client = $xml->createElement('client');
        $packet->appendChild($client);
        $set = $xml->createElement('set');
        $client->appendChild($set);
        $filter = $xml->createElement('filter');
        $set->appendChild($filter);
        $id = $xml->createElement('id', $clientinfo->id);
        $filter->appendChild($id);
        $values = $xml->createElement('values');
        $set->appendChild($values);
        $limits = $xml->createElement('limits');
        $values->appendChild($limits);
        $resourcepolicy = $xml->createElement('resource-policy');
        $limits->appendChild($resourcepolicy);
        $overuse = $xml->createElement('overuse', 'notify');
        $resourcepolicy->appendChild($overuse);
        
        return $xml->saveXML();
    }
    
    /**
     * PleskApi::setClientOveruseLimit()
     * 
     * @param mixed $host
     * @param mixed $client
     * @return
     */
    public function setClientOveruseLimit($host, $client){
        $curl = $this->curlInit($host);
        $packet = $this->clientSetOveruseLimitPacket($client);
        $response = $this->sendRequest($curl, $packet);
        if($this->parseResponse($response)){
            return true;
        }
    }
    
    /**
     * PleskApi::domainSetOveruseLimitPacket()
     * 
     * @param mixed $domaininfo
     * @return
     */
    private function domainSetOveruseLimitPacket($domaininfo){
        // Create new XML document.
        $xml = new DomDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;
        
        // Create packet
        $packet = $xml->createElement('packet');
        $packet->setAttribute('version', '1.6.0.0');
        $xml->appendChild($packet);
        $domain = $xml->createElement('domain');
        $packet->appendChild($domain);
        $set = $xml->createElement('set');
        $domain->appendChild($set);
        $filter = $xml->createElement('filter');
        $set->appendChild($filter);
        $id = $xml->createElement('id', $domaininfo->id);
        $filter->appendChild($id);
        $values = $xml->createElement('values');
        $set->appendChild($values);
        $limits = $xml->createElement('limits');
        $values->appendChild($limits);
        $overuse = $xml->createElement('overuse', 'notify');
        $limits->appendChild($overuse);
        
        return $xml->saveXML();
    }
    
    /**
     * PleskApi::setDomainOveruseLimit()
     * 
     * @param mixed $host
     * @param mixed $domain
     * @return
     */
    public function setDomainOveruseLimit($host, $domain){
        $curl = $this->curlInit($host);
        $packet = $this->domainSetOveruseLimitPacket($domain);
        $response = $this->sendRequest($curl, $packet);
        if($this->parseResponse($response)){
            return true;
        }
    }
    
    /**
     * PleskApi::clientAddIpPoolPacket()
     * 
     * @param mixed $host
     * @param mixed $clientinfo
     * @return
     */
    private function clientAddIpPoolPacket($host, $clientinfo){
        // Create new XML document.
        $xml = new DomDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;
        
        // Create packet
        $packet = $xml->createElement('packet');
        $packet->setAttribute('version', '1.4.2.0');
        $xml->appendChild($packet);
        $client = $xml->createElement('client');
        $packet->appendChild($client);
        $ippool_add_ip = $xml->createElement('ippool_add_ip');
        $client->appendChild($ippool_add_ip);
        $client_id = $xml->createElement('client_id', $clientinfo->id);
        $ippool_add_ip->appendChild($client_id);
        $ip_address = $xml->createElement('ip_address', $host['host_ipaddress']);
        $ippool_add_ip->appendChild($ip_address);
        return $xml->saveXML();
    }

    /**
     * PleskApi::clientAddIpPool()
     * 
     * @param mixed $host
     * @param mixed $client
     * @return
     */
    public function clientAddIpPool($host, $client){
        $curl = $this->curlInit($host);
        $packet = $this->clientAddIpPoolPacket($host, $client);
        $response = $this->sendRequest($curl, $packet);
        if($this->parseResponse($response)){
            return true;
        }
    }

    /**
     * PleskApi::clientSetPacket()
     * 
     * @param mixed $clientinfo
     * @param mixed $params
     * @return
     */
    private function clientSetPacket($clientinfo, $params){
        // Create new XML document.
        $xml = new DomDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;
        
        // Create packet element
        $packet = $xml->createElement('packet');
        
        // Get host from database and set version as attribute to element packet.
        $packet->setAttribute('version', '1.4.1.0');
        $xml->appendChild($packet);
        $client = $xml->createElement('client');
        $packet->appendChild($client);
        $set = $xml->createElement('set');
        $client->appendChild($set);
        $filter = $xml->createElement('filter');
        $set->appendChild($filter);
        $id = $xml->createElement('id', $clientinfo->id);
        $filter->appendChild($id);
        $values = $xml->createElement('values');
        $set->appendChild($values);
        foreach($params as $name => $pvalues) { 
            $node = $xml->createElement($name); 
            $values->appendChild($node); 
            foreach ($pvalues as $key => $value) { 
                $xmlelement = $xml->createElement($key, $value); 
                $node->appendChild($xmlelement); 
            } 
        }
        return $xml->saveXML();
    }

    /**
     * PleskApi::setClient()
     * 
     * @param mixed $host
     * @param mixed $client
     * @param mixed $params
     * @return
     */
    public function setClient($host, $client, $params){
        $curl = $this->curlInit($host);
        $packet = $this->clientSetPacket($client, $params);
        $response = $this->sendRequest($curl, $packet);
        if($this->parseResponse($response)){
            return true;
        }
    }

    /**
     * PleskApi::domainCreatePacket()
     * 
     * @param mixed $params
     * @return
     */
    private function domainCreatePacket($params){
        // Create new XML document.
        $xml = new DomDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;
        
        // Create packet
        $packet = $xml->createElement('packet');
        $packet->setAttribute('version', '1.4.2.0');
        $xml->appendChild($packet);
        $domain = $xml->createElement('domain');
        $packet->appendChild($domain);
        $add = $xml->createElement('add');
        $domain->appendChild($add);

        // Add general setup and optional parameters 
        foreach($params as $name => $values) { 
            $node = $xml->createElement($name); 
            $add->appendChild($node); 
            if($name == 'hosting'){
                $node2 = $xml->createElement($params['gen_setup']['htype']);
                $node->appendChild($node2);
                $node = $node2;
            }
            foreach ($values as $key => $value) { 
                $xmlelement = $xml->createElement($key, $value); 
                $node->appendChild($xmlelement); 
            } 
        } 
        return $xml->saveXML();
    }

    /**
     * PleskApi::createDomain()
     * 
     * @param mixed $host
     * @param mixed $params
     * @return
     */
    public function createDomain($host, $params){
        $curl = $this->curlInit($host);
        $packet = $this->domainCreatePacket($params);
        $response = $this->sendRequest($curl, $packet);
        if($this->parseResponse($response)){
            return true;
        }
    }
    
    /**
     * PleskApi::domainSetPacket()
     * 
     * @param mixed $domain
     * @param mixed $params
     * @return
     */
    private function domainSetPacket($domain, $params){
        // Create new XML document.
        $xml = new DomDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;
        
        // Create packet
        $packet = $xml->createElement('packet');
        $packet->setAttribute('version', '1.4.2.0');
        $xml->appendChild($packet);
        $client = $xml->createElement('domain');
        $packet->appendChild($client);
        $set = $xml->createElement('set');
        $client->appendChild($set);
        $filter = $xml->createElement('filter');
        $set->appendChild($filter);
        $domain_name = $xml->createElement('domain_name', $domain->data->gen_info->name);
        $filter->appendChild($domain_name);
        $values = $xml->createElement('values');
        $set->appendChild($values);
        foreach($params as $name => $pvalues) { 
            $node = $xml->createElement($name); 
            $values->appendChild($node); 
            
            if($name == 'hosting'){
                $node2 = $xml->createElement($domain->data->gen_info->htype);
                $node->appendChild($node2);
                $node = $node2;
            }
            
            foreach ($pvalues as $key => $value) { 
                $xmlelement = $xml->createElement($key, $value); 
                $node->appendChild($xmlelement); 
            } 
        }
        return $xml->saveXML();
    }

    /**
     * PleskApi::setDomain()
     * 
     * @param mixed $host
     * @param mixed $domain
     * @param mixed $params
     * @return
     */
    public function setDomain($host, $domain, $params){
        $curl = $this->curlInit($host);
        $packet = $this->domainSetPacket($domain, $params);
        $response = $this->sendRequest($curl, $packet);
        if($this->parseResponse($response)){
            return true;
        }
    }
    
     /**
     * PleskApi::domainSetScriptingPacket()
     * 
     * @param mixed $domain
     * @param mixed $params
     * @return
     */
    private function domainSetScriptingPacket($domain, $params){
        // Create new XML document.
        $xml = new DomDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;
        
        // Create packet
        $packet = $xml->createElement('packet');
        $packet->setAttribute('version', '1.5.0.0');
        $xml->appendChild($packet);
        $client = $xml->createElement('domain');
        $packet->appendChild($client);
        $set = $xml->createElement('set');
        $client->appendChild($set);
        $filter = $xml->createElement('filter');
        $set->appendChild($filter);
        $domain_name = $xml->createElement('domain_name', $domain->data->gen_info->name);
        $filter->appendChild($domain_name);
        $values = $xml->createElement('values');
        $set->appendChild($values);
        $hosting = $xml->createElement('hosting');
        $values->appendChild($hosting);
        $vrt_hst = $xml->createElement('vrt_hst');
        $hosting->appendChild($vrt_hst);
        $property = $xml->createElement('property');
        $vrt_hst->appendChild($property);
        $name = $xml->createElement('name', 'php');
        $property->appendChild($name);
        $value = $xml->createElement('value', $params['scripting']);
        $property->appendChild($value);
        $ip_address = $xml->createElement('ip_address', $params['ip_address']);
        $vrt_hst->appendChild($ip_address);
        return $xml->saveXML();
    }

    /**
     * PleskApi::setDomainScripting()
     * 
     * @param mixed $host
     * @param mixed $domain
     * @param mixed $params
     * @return
     */
    public function setDomainScripting($host, $domain, $params){
        $curl = $this->curlInit($host);
        $packet = $this->domainSetScriptingPacket($domain, $params);
        $response = $this->sendRequest($curl, $packet);
        if($this->parseResponse($response)){
            return true;
        }
    }
    
    /**
     * PleskApi::domainAliasCreatePacket()
     * 
     * @param mixed $domain
     * @param mixed $alias
     * @return
     */
    private function domainAliasCreatePacket($domain, $alias){
        // Create new XML document.
        $xml = new DomDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;
        
        // Create packet
        $packet = $xml->createElement('packet');
        $packet->setAttribute('version', '1.4.2.0');
        $xml->appendChild($packet);
        $domain_alias = $xml->createElement('domain_alias');
        $packet->appendChild($domain_alias);
        $create = $xml->createElement('create');
        $domain_alias->appendChild($create);
        $domain_id = $xml->createElement('domain_id', $domain->id);
        $create->appendChild($domain_id);
        $name = $xml->createElement('name', $alias);
        $create->appendChild($name);
        return $xml->saveXML();
    }
    
    /**
     * PleskApi::createDomainAlias()
     * 
     * @param mixed $host
     * @param mixed $domain
     * @param mixed $alias
     * @return
     */
    public function createDomainAlias($host, $domain, $alias){
        $curl = $this->curlInit($host);
        $packet = $this->domainAliasCreatePacket($domain, $alias);
        $response = $this->sendRequest($curl, $packet);
        if($this->parseResponse($response)){
            return true;
        }
    }
    
    /**
     * PleskApi::emailCreatePacket()
     * 
     * @param mixed $domain
     * @param mixed $params
     * @return
     */
    private function emailCreatePacket($domain, $params){
        // Create new XML document.
        $xml = new DomDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;
        
        // Create packet
        $packet = $xml->createElement('packet');
        $packet->setAttribute('version', '1.4.2.0');
        $xml->appendChild($packet);
        $mail = $xml->createElement('mail');
        $packet->appendChild($mail);
        $create = $xml->createElement('create');
        $mail->appendChild($create);
        $filter = $xml->createElement('filter');
        $create->appendChild($filter);
        $domain_id = $xml->createElement('domain_id', $domain->id);
        $filter->appendChild($domain_id);
        $mailname = $xml->createElement('mailname');
        $filter->appendChild($mailname);
        foreach ($params as $key => $value) { 
            $node = $mailname;
            if(strpos($key, ':') !== false){
                $split = explode(':', $key);
                $key = $split[1];
                $node = ${$split[0]};
                if(!isset(${$split[0]})){
                    ${$split[0]} = $xml->createElement($split[0]);
                    $mailname->appendChild(${$split[0]});
                }
                $xmlelement = $xml->createElement($key, $value);
                ${$split[0]}->appendChild($xmlelement);
            }else{
                $xmlelement = $xml->createElement($key, $value); 
                $node->appendChild($xmlelement);
            }
        }
        return $xml->saveXML();
    }
    
    /**
     * PleskApi::createEmail()
     * 
     * @param mixed $host
     * @param mixed $domain
     * @param mixed $params
     * @return
     */
    public function createEmail($host, $domain, $params){
        $curl = $this->curlInit($host);
        $packet = $this->emailCreatePacket($domain, $params);
        $response = $this->sendRequest($curl, $packet);
        if($this->parseResponse($response)){
            return true;
        }
    }
    
    /**
     * PleskApi::emailSetPacket()
     * 
     * @param mixed $domain
     * @param mixed $params
     * @return
     */
    private function emailSetPacket($domain, $params){
        // Create new XML document.
        $xml = new DomDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;
        
        // Create packet
        $packet = $xml->createElement('packet');
        $packet->setAttribute('version', '1.4.2.0');
        $xml->appendChild($packet);
        $mail = $xml->createElement('mail');
        $packet->appendChild($mail);
        $create = $xml->createElement('create');
        $mail->appendChild($create);
        $filter = $xml->createElement('filter');
        $create->appendChild($filter);
        $domain_id = $xml->createElement('domain_id', $domain->id);
        $filter->appendChild($domain_id);
        $mailname = $xml->createElement('mailname');
        $filter->appendChild($mailname);
        
        
        foreach ($params as $key => $value) { 
            if(strpos($key, 'mailbox:') !== false){
                $key = substr($key, strpos($key, ':')+1);
                if(!isset($mailbox)){
                    $mailbox = $xml->createElement('mailbox');
                    $mailname->appendChild($mailbox);
                }
                $xmlelement = $xml->createElement($key, $value);
                $mailbox->appendChild($xmlelement);
            }else if(strpos($key, 'perm:') !== false){
                $key = substr($key, strpos($key, ':')+1);
                if(!isset($permissions)){
                    $permissions = $xml->createElement('permissions');
                    $mailname->appendChild($permissions);
                }
                $xmlelement = $xml->createElement($key, $value);
                $permissions->appendChild($xmlelement);
            }else{
                $xmlelement = $xml->createElement($key, $value); 
                $mailname->appendChild($xmlelement); 
            }
        }
        return $xml->saveXML();
    }
    
    /**
     * PleskApi::setEmail()
     * 
     * @param mixed $host
     * @param mixed $domain
     * @param mixed $params
     * @return
     */
    public function setEmail($host, $domain, $params){
        $curl = $this->curlInit($host);
        $packet = $this->emailCreatePacket($domain, $params);
        $response = $this->sendRequest($curl, $packet);
        if($this->parseResponse($response)){
            return true;
        }
    }
    
}

?>