<?php

function DM_ECHO($message,$resultcount=0,$results=array(),$die=true){
    $echo = array("message"=>$message,"counts"=>$resultcount,"results"=>$results);
    echo json_encode($echo);
    if($die)
        die;
}

spl_autoload_register(function ($name){
    require_once(ABSPATH.'engines/class.'.$name.'.php');
});
