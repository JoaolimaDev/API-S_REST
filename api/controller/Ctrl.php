<?php 
namespace Controller;
require_once("Log.php");

use Login;

class Ctrl
{

    
    public static function sethAuth($auth)
    {
        require_once("Auth.php");
        $resp = Auth::auth($auth);
        return $resp;
    }

    public static function getRole($data)
    {
        $obj = new Log;
        $resp = $obj->valida($data);
        return $resp;
    }
    
    public static function setID($data)
    {
        $obj = new Log;
        $resp = $obj->Getid($data);
        return $resp;
    }

    public static function setLog($token, $session, $menuop, $id, $data){
        require_once("api/Login.php");
	    new Login($token, $session, $menuop, $id, $data);

    }



}






?>