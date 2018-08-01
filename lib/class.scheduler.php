<?php
/*
 * 调度器
 *
 */

class Scheduler{
    /*
     * 支持的引擎
     */
    public $SupportEngines = array();

    /*
     * 默认引擎
     */
    public $defaultEngine = 'bing';

    /*
     * 使用哪些引擎
     */
    public $EngineInUse = array();

    /*
     * 获取的action动作
     */
    public $action;

    /*
     * 储存的搜索结果对象
     */
    public $results = array();

    /*
     * 查询的字符串
     */
    public $query;

    /*
     * 需要的结果条数
     */
    public $count;

    /*
     * offset
     */
    public $offset;

    /*
     * 查询Key
     */
    private $requestkey;

    public function __construct(){
        $this->action = isset($_GET['action']) ? $_GET['action'] : 'search';
        $this->query = isset($_GET['q']) ? $_GET['q'] : NULL ;
        $this->count = isset($_GET['count']) ? $_GET['count'] : 10;
        $this->offset = isset($_GET['offset']) ? $_GET['offset'] : 0;
        $this->requestkey = isset($_GET['key']) ? $_GET['key'] : '';
        $this->EngineInUse = isset($_GET['engine']) ? $_GET['engine'] : array('all') ;
        $this->Format = isset($_GET['format']) ? $_GET['format'] : 'json';
        $this->init_engines();
    }

    public function init_engines(){
        $dir= "./engines";
        if(!is_dir($dir))
            DM_ECHO('SystemError, Failed!',$this->query);
        $fd = opendir($dir);
        while(($file=readdir($fd))!==false){
            preg_match('/class\.([^.]*)\.php/',$file,$m);
            if(!empty($m[1]))
                $this->SupportEngines[] = $m[1];
        }
    }

    public function run(){
        $this->send_header();
        if($this->requestkey != KEY)
            DM_ECHO('REQEUST KEY INVALID, Failed!',$this->query);
        if($this->action=='listengine'){
            $this->showEngines();
        }elseif($this->action=='search'){
            $this->get_Results();
        }
        if($this->Format == 'json')
            echo DM_ECHO('Get Result Success!',$this->query,count($this->results),$this->results);
        else
            echo DM_RESULTS_TO_HTML($this->query,$this->results);
    }

    public function get_Results(){
        if($this->query==NULL)
            DM_ECHO('Not Specified Search Key, Failed!',$this->query);
        if(in_array('all',$this->EngineInUse)){
            $engines = $this->SupportEngines;
        }else{
            $engines = array_intersect($this->SupportEngines,$this->EngineInUse);
        }
        foreach($engines as $engine){
            $sen = new $engine();
            $results = $sen->get_Results($this->query,$this->count);
            foreach($results as $result)
                array_push($this->results,$result);
        }
    }

    public function showEngines(){
        DM_ECHO('Support Engines',$this->query,count($this->SupportEngines),$this->SupportEngines); 
    }

    public function send_header(){
        if($this->Format=='json')
        header("Content-Type:application/json");
    }
}
