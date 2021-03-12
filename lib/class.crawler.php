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
    public $header=array('Accept: text/html,application/xhtml+xml,application/xml;application/json;q=0.9,*/*;q=0.8','Accept-Encoding: gzip,identity','Accept-Language: en-US,en;q=0.8','Connection: keep-alive');

    //HTTP 返回header
    public $ResponseHeader=array();

    //HTTP 返回CODE
    public $ResponseCode;

    //HTTP 返回主体
    public $ResponseBody;

    private $cookies = array();

    private $cookiekey = array('domain', 'expires', 'path', 'HttpOnly', 'secure', 'SameSite');

    public function __construct($url = '', $cookiefile = false){
        $this->url = $url;
        $this->useragent = UserAgent;
        $this->timeout = TimeOut;
        if($cookiefile == false)
            $this->cookiefile = COOKIEFILE;

        if(file_exists($this->cookiefile) && time() - filemtime($this->cookiefile) > COOKREFRESH){
            unlink($this->cookiefile);
            $this->cookies = array();
        }elseif(file_exists($this->cookiefile)){
            $this->cookies = unserialize(file_get_contents($this->cookiefile));
        }elseif(!file_exists($this->cookiefile)){
            $this->cookies = array();
        }
    }

    public function add_posts($key, $value){
        $this->posts[$key] = $value;
    }

    public function crawl($url=false){
        if($url==false)
            $url = $this->url;
        if($url=='')
            DM_ECHO('Invalid crawl URL, Failed!');
        $curl = curl_init(); 
        //设置URL
        curl_setopt($curl, CURLOPT_URL, $url);
        //设置用户代理
        curl_setopt($curl, CURLOPT_USERAGENT, $this->useragent);
        //设置超时时间
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
        //自动重定向
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        //重定向加Refer
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
        //输出头文件
        curl_setopt($curl, CURLOPT_HEADER, 1);
        //输出内容
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        //不检查证书主机
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        //不检查证书
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        //附加cookie
        if(!empty($this->get_cookie($url)))
            curl_setopt($curl, CURLOPT_COOKIE, $this->get_cookie($url));
        //设置其他头部
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->header);
        //设置可以获取请求头信息
        curl_setopt($curl, CURLINFO_HEADER_OUT, 1);
        if(!empty($this->posts)){
            curl_setopt($curl, CURLOPT_POST, 1); 
            curl_setopt($curl, CURLOPT_POSTFIELDS, $this->posts);
        }
        $data = curl_exec($curl);
        if(curl_errno($curl)){
            return curl_error($curl);
        }
        //Response CODE
        $this->ResponseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $headerlen = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $header = substr($data, 0, $headerlen);
        $html = substr($data, $headerlen);
        $this->parse_header($header);
        if(isset($this->ResponseHeader['Content-Encoding']) && $this->ResponseHeader['Content-Encoding'] == 'gzip'){
            $html = gzdecode($html);
        }
        curl_close($curl);
        $this->ResponseBody = $html;
    }

    public function get_ResponseBody(){
        return $this->ResponseBody;
    }

    private function parse_header($string){
        $lines = explode("\r\n", $string);
        foreach($lines as $line){
            if( $line== '' || stripos($line, 'HTTP/') === 0) continue;
            $p = strpos($line, ':');
            $k = trim(substr($line, 0, $p));
            $v = trim(substr($line, $p+1)); 
            if($k == 'Set-Cookie'){
                $cookie = $this->parse_cookie($v);
                $key = $this->cookie_exist_key($cookie['name']);
                if($key === false){
                    array_push($this->cookies, $cookie);
                }else{
                    $this->cookies[$key] = $cookie;
                }
            }
            $this->ResponseHeader[$k] = trim($v);
        }
    }

     private function parse_cookie($cookie_data){
        $cols = explode(';', $cookie_data);
        $cookie = array();
        foreach($cols as $col){
            $p = strpos($col, '=');
            if($p === false){
                $k = trim($col);
                $v = true;
            }else{
                $k = trim(substr($col, 0, $p));
                $v = trim(substr($col, $p+1));
            }
            if(!in_array($k, $this->cookiekey)){
                $cookie = array('name'=>$k, 'value'=>$v);
            }elseif($v !== false){
                $cookie = array_merge($cookie, array($k=>$v));
            }
            if($p === false){
                $cookie = array_merge($cookie, array($k=>$v));
            }
        }
        return $cookie;
    }

    private function get_cookie($url){
        preg_match('/(https?):\/\/([^\/:]*)(\d+)?([^\?]+)/i', $url, $matches);
        $is_ssl = $matches[1] == 'https';
        $host = $matches[2];
        $port = $matches[3];
        $path = $matches[4];
        $cookies = '';
        foreach($this->cookies as $cookie){
            $cname = $cookie['name'];
            $cvalue = $cookie['value'];
            $cexpires = isset($cookie['expires']) ? strtotime($cookie['expires']) : time() + 10;
            $cdomain = isset($cookie['domain']) ? $cookie['domain'] : $host;
            $cpath = isset($cookie['path']) ? $cookie['path'] : '/';
            $csecure = isset($cookie['secure']) ? $cookie['secure'] : false;
            if($cexpires > time() && stripos($host, $cdomain) >= 0){
                $cookies .= sprintf('%s=%s; ', $cname, $cvalue);
            }
        }
        return rtrim($cookies, '; ');
    }

    private function cookie_exist_key($cookiename){
        if(empty($this->cookies)) return false;
        foreach($this->cookies as $key=>$cookie){
            if($cookie['name'] == $cookiename) return $key;
        }
        return false;
    }

    public function __destruct(){
        if(!empty($this->cookies))
            file_put_contents($this->cookiefile, serialize($this->cookies));
    }
}
