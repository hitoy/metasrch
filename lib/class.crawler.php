<?php
/*
 * 爬行蜘蛛类
 * Require Extension CURL
 */

class Crawler{
    //爬行的网址URL
    public $url;

    //HTTP post数据
    public $posts=array();

    //HTTP cookie数据
    public $cookie;

    //HTTP User-Agent
    public $useragent;

    //请求超时
    public $timeout;

    //其他header信息
    public $header=array('Accept: text/html,application/xhtml+xml,application/xml;application/json;q=0.9,*/*;q=0.8','Accept-Encoding: gzip, identity','Accept-Language: en-US,en;q=0.8','Connection: keep-alive');

    //HTTP 返回header
    public $ResponseHeader=array();

    //HTTP 返回CODE
    public $ResponseCode;

    //HTTP 返回主体
    public $ResponseBody;

    public function __construct($url='',$cookiefile=false){
        $this->url = $url;
        $this->useragent = UserAgent;
        $this->timeout = TimeOut;
        if($cookiefile==false)
            $this->cookiefile = COOKIEFILE;
        if(!file_exists($this->cookiefile))
            touch($this->cookiefile);
        else{
            if(time() - filectime($this->cookiefile) > COOKREFRESH){
                unlink($this->cookiefile);
                touch($this->cookiefile);
            }
        }
    }

    public function add_posts($key,$value){
        $this->posts[$key] = $value;
    }

    public function crawl($url=false){
        if($url==false)
            $url = $this->url;
        if($url=='')
            DM_ECHO('Invalid crawl URL, Failed!');
        $curl = curl_init(); 
        //设置URL
        curl_setopt($curl,CURLOPT_URL,$url);
        //设置用户代理
        curl_setopt($curl,CURLOPT_USERAGENT, $this->useragent);
        //设置超时时间
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
        //自动重定向
        curl_setopt($curl,CURLOPT_FOLLOWLOCATION, 1);
        //重定向加Refer
        curl_setopt($curl,CURLOPT_AUTOREFERER, 1);
        //输出头文件
        curl_setopt($curl,CURLOPT_HEADER,1);
        //输出内容
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
        //不检查证书主机
        curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,0);
        //不检查证书
        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,0);
        //设置其他头部
        curl_setopt($curl,CURLOPT_HTTPHEADER, $this->header);
        //设置COOKIE
        curl_setopt($curl, CURLOPT_COOKIEFILE,$this->cookiefile);
        curl_setopt($curl, CURLOPT_COOKIEJAR,$this->cookiefile);
        //设置可以获取请求头信息
        curl_setopt($curl, CURLINFO_HEADER_OUT,1);
        if(!empty($this->posts)){
            curl_setopt($curl,CURLOPT_POST,1); 
            curl_setopt($curl,CURLOPT_POSTFIELDS, $this->posts);
        }
        $data = curl_exec($curl);
        if(curl_errno($curl)){
            return curl_error($curl);
        }
        //Response CODE
        $this->ResponseCode = curl_getinfo($curl,CURLINFO_HTTP_CODE);
        $headerlen = curl_getinfo($curl,CURLINFO_HEADER_SIZE);
        $header = substr($data,0,$headerlen);
        $html = substr($data,$headerlen);
        $this->parse_header($header);
        if($this->ResponseHeader['Content-Encoding'] == 'gzip'){
            $html = gzdecode ($html);
        }
        curl_close($curl);
        $this->ResponseBody = $html;
    }

    public function parse_header($string){
        $lines = explode("\r\n",$string);
        foreach($lines as $line){
            if( $line== '' || stripos($line,'HTTP/') === 0) continue;
            list($k,$v) = explode(":",$line);
            $this->ResponseHeader[$k] = trim($v);
        }
        if(!empty($this->ResponseHeader['Set-Cookie'])){
            $this->cookie = $this->ResponseHeader['Set-Cookie'];
        }
    }

    public function get_ResponseBody(){
        return $this->ResponseBody;
    }
}
