<?php

/**
 * @author Jeffro
 * @copyright 2011
 */

class HostingTemplates
{    
    function getTemplate($name)
    {
        if($this->templateExists($name)){
            $database = new Database2();
            return $database->getTableRow("hosting_pakketten", "pakketnaam", $name);
        }else{
            throw new Exception("Pakket staat niet in de database.");
        }
    }
    
    function templateExists($name){
        $database = new Database2();
        if($database->objectExists("SELECT pakketnaam FROM hosting_pakketten WHERE pakketnaam = '" . $name . "'") == true){
            return true;
        }else{
            return false;
        }
    }
}

?>