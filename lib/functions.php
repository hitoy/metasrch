<?php

function DM_ECHO($message,$query="",$resultcount=0,$results=array(),$die=true){
    $echo = array("message"=>$message,"query"=>$query,"counts"=>$resultcount,"results"=>$results);
    echo json_encode($echo);
    if($die)
        die;
}
function DM_RESULTS_TO_HTML($query,$results){
    $html="<!DOCTYPE HTML><html><head><meta charset=\"utf-8\"><title>$query</title></head><body>";
    foreach($results as $result){
        $html .= "<h2>".$result["title"]."</h2>\r\n";
        $html .="<p>".$result['description']."</p>\r\n";
    }
    return $html."</body></html>";
}
spl_autoload_register(function ($name){
    require_once(ABSPATH.'engines/class.'.$name.'.php');
});
