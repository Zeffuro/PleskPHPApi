<?php

/**
 * @author Jeffro
 * @copyright 2011
 */
 
class Hosts
{    
    function addhost($params)
    {
        $database = new Database();
        if($database->query("INSERT INTO hosts (host_address, host_ipaddress, host_port, host_path, host_user, host_pass, host_key, host_authmethod, host_version) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)", $params)){
            return true;
        }else{
            return false;
        }
    }
    
    function edithost($id, $params)
    {
        $database = new Database();
        if($database->query("UPDATE hosts SET host_address = ?, host_ipaddress = ?, host_port = ?, host_path = ?, host_version = ? WHERE host_id = $id", $params)){
            return true;
        }else{
            return false;
        }
    }
    
    function delhost($host){
        $params = array($host);
        $database = new Database();
        if($database->query("DELETE FROM hosts WHERE host_address = ?", $params)){
            return true;
        }else{
            return false;
        }
    }
    
    function gethost($host){
        if($this->hostexists($host)){
            $database = new Database();
            return $database->getTableRow("hosts", "host_address", $host);
        }else{
            throw new Exception("Host staat niet in de database.");
        }
    }
    
    function gethostbyid($host){
        if($this->hostexistsbyid($host)){
            $database = new Database();
            return $database->getTableRow("hosts", "host_id", $host);
        }else{
            throw new Exception("Host staat niet in de database.");
        }
    }
    
    function gethosts(){
        $database = new Database();
        return $database->getTable("hosts");
        
    }

    function hostexists($host){
        $database = new Database();
        if($database->objectExists("SELECT host_id FROM hosts WHERE host_address = '" . $host . "'") == true){
            return true;
        }else{
            return false;
        }
    }
    
    function hostexistsbyid($host){
        $database = new Database();
        if($database->objectExists("SELECT host_id FROM hosts WHERE host_id = '" . $host . "'") == true){
            return true;
        }else{
            return false;
        }
    }
}

?>