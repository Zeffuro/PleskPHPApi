<?php

/**
 * @author Jeffro
 * @copyright 2011
 */

class Orders
{    
    function getOrder($id)
    {
        if($this->orderExists($id)){
            $database = new Database2();
            return $database->getTableRow("orders", "orderid", $id);
        }else{
            throw new Exception("Order staat niet in de database.");
        }
    }
    
    function orderExists($id){
        $database = new Database2();
        if($database->objectExists("SELECT orderid FROM orders WHERE orderid = '" . $id . "'") == true){
            return true;
        }else{
            return false;
        }
    }
    
    function createOrder($params){
        $database = new Database2();
        if($database->query("INSERT INTO orders (orderid, domein, ext, ip, pakket, klantnummer) VALUES (?, ?, ?, ?, ?, ?)", $params)){
            return true;
        }else{
            return false;
        }
    }
}

?>