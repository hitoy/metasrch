<?php
/*
 * Bing 搜索结果
 *
 */
class bing{
    public $spider;
    public $count;
    public $results = array();
    public $baseurl;
    public $entryurl;

    public function __construct(){
        $this->spider = new Crawler();
        $this->entryurl = 'https://www.bing.com/?ensearch=1';
        $this->baseurl = 'https://www.bing.com/search?q=%s&first=%d&FORM=PERE';
        $this->spider->crawl($this->entryurl);
    }

    /*
     * 获取结果
     * @param $q query string
     * @param $c count
     */
    public function get_Results($q,$c){
        $q = urlencode($q);
        $start = 0;
        $retry = 10;
        while(true){
            //URL
            $url = sprintf($this->baseurl, $q, $start);
            //爬行
            $this->spider->crawl($url);
            //HTTP CODE
            $ResponseCode = $this->spider->ResponseCode;
            //HTTP HTML
            $ResponseBody = $this->spider->get_ResponseBody();

            //本页采集的列表
            $searchlist = $this->parse_result($ResponseBody);
            if(!empty($searchlist)){
                $start = $start + count($searchlist);
                $this->results = array_merge($this->results, $searchlist);
            }
            //如果达到目标数量要求，或者重试次数用完，退出循环
            if(count($this->results) >= $c || $retry <= 0){
                break;
            }
            $retry--;
        }
        return $this->results;
    }

    public function parse_result($string){
        /*
        $xml = simplexml_load_string($string);
        $returned = array();
        if(isset($xml->channel->item)){
            foreach($xml->channel->item as $item){
                $title =$item->title->__toString();
                $description = $item->description->__toString();
                $link = $item->link->__toString();
                $pubdate = $item->pubDate->__toString();
                $result = array('title'=>$title, 'description'=>$description, 'link'=>$link, 'pubdate'=>$pubdate);
                $key = sha1($item->title.$item->description);
                $returned[$key] = $result;
            }
        }
        print_r($returned);
        return $returned;

         */
        preg_match_all("/<li class=\"b_algo\">(.*?)<\/li>/isu",$string,$m);
        if(empty($m[1])) return false;
        $returned = array();
        foreach($m[1] as $result){
            preg_match("/<h2>(.*?)<\/h2>/isu",$result,$m);
            $title =  html_entity_decode(strip_tags($m[1]));
            $title = str_replace(array('...', '|'), '', $title);

            preg_match("/<p>(.*?)<\/p>/isu",$result,$m);
            $description =  html_entity_decode(strip_tags($m[1]));
            $description = preg_replace('/^.*?· /isu', '', $description);

            preg_match("/<cite>(.*?)<\/cite>/isu",$result,$m);
            $url =  html_entity_decode(strip_tags($m[1]));
            $key = sha1($title.$description);
            $result = array('title'=>$title,'url'=>$url,'description'=>$description);
            $returned[$key] = $result;
        }
        return $returned;
    }
}
