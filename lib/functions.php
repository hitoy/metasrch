<?php

function DM_ECHO($message,$resultcount=0,$results=array(),$die=true){
    $echo = array("message"=>$message,"counts"=>$resultcount,"results"=>$results);
    echo json_encode($echo);
    if($die)
        die;
}
function DM_RESULTS_TO_HTML($results){
    $html="";
    foreach($results as $result){
        $html .= "<h2>".$result["title"]."</h2>\r\n";
        $html .="<p>".$result['description']."</p>\r\n";
    }
    return $html;
}
spl_autoload_register(function ($name){
    require_once(ABSPATH.'engines/class.'.$name.'.php');
});
