<?php

/**
 * @author Jeffro
 * @copyright 2011
 */

class Customers
{    
    function getCustomer($id)
    {
        if($this->customerExists($id)){
            $database = new Database2();
            //return $database->getObject("SELECT klantid, clogin, eig_bedrijfsnaam, eig_voorletters, eig_tussenvoegsel, eig_naam, AES_DECRYPT(np,'" . AESKEY . "') as pasw from klanten where klantid='$id'");
            return $database->getTableRow("klanten", "klantid", $id);
        }else{
            throw new Exception("Klant staat niet in de database.");
        }
    }
    
    function customerExists($id){
        $database = new Database2();
        if($database->objectExists("SELECT klantid FROM klanten WHERE klantid = '" . $id . "'") == true){
            return true;
        }else{
            return false;
        }
    }
    
    function createCustomer($params){
        $database = new Database2();
        if($database->query("INSERT INTO klanten (klantid, eig_voorletters, eig_tussenvoegsel, eig_naam, clogin, pasw) VALUES (?, ?, ?, ?, ?, ?)", $params)){
            return true;
        }else{
            return false;
        }
    }
}

?>