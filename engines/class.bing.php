<?php
/*
 * Bing 搜索结果
 *
 */
class bing{
    public $spider;
    public $count;
    public $results=array();
    public $baseurl;

    public function __construct(){
        $this->spider = new Crawler();
        $this->baseurl = "https://www.bing.com/search?q=%s&first=%d";
    }

    /*
     * 获取结果
     * @param $q query string
     * @param $c count
     */
    public function get_Results($q,$c){
        $q= urlencode($q);
        $retry = 3;
        while(true){
            //Bing的first指令为偏移值
            $first = count($this->results)+1;
            //URL
            $url = sprintf($this->baseurl,$q,$first);
            //爬行
            $this->spider->crawl($url);
            //HTTP CODE
            $ResponseCode = $this->spider->ResponseCode;
            //HTTP HTML
            $ResponseBody = $this->spider->get_ResponseBody();
            //本页采集的列表
            $searchlist = $this->parse_result($ResponseBody);
            //如果达到目标数量要求，或者重试次数用完，退出循环
            if(count($this->results) >= $c || $retry == 0){
                break;
            }
            //重试: 页面没有内容，返回CODE码不对 并且重试次数未达限制
            if( (empty($searchlist) || $this->spider->ResponseCode != '200') && $retry > 0){
                $retry--;
                continue;
            }

        }
        return $this->results;
    }

    public function parse_result($string){
        preg_match_all("/<li class=\"b_algo\">(.*?)<\/li>/isu",$string,$m);
        if(empty($m[1])) return false;
        $returned=array();
        foreach($m[1] as $result){
            preg_match("/<h2>(.*?)<\/h2>/isu",$result,$m);
            $title =  html_entity_decode(strip_tags($m[1]));

            preg_match("/<p>(.*?)<\/p>/isu",$result,$m);
            $description =  html_entity_decode(strip_tags($m[1]));

            preg_match("/<cite>(.*?)<\/cite>/isu",$result,$m);
            $url =  html_entity_decode(strip_tags($m[1]));

            $result = array('title'=>$title,'url'=>$url,'description'=>$description);
            array_push($returned,$result);
            array_push($this->results,$result);
        }
        return $returned;
    }
}
