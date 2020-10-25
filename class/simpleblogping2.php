<?php
if (!defined('XOOPS_ROOT_PATH') || !is_file(XOOPS_ROOT_PATH . '/class/snoopy.php')) {
    exit();
}
require_once XOOPS_ROOT_PATH . '/class/snoopy.php';

class SimpleBlogPing2
{
    public $url;

    public $title;

    public $excerpt;

    public $blog_name;

    public $rss;

    public $timeout = 10000;

    public $DEBUG = false;

    public function __construct($rss, $url, $blog_name = null, $title = null, $excerpt = null)
    {
        $this->rss = $rss;

        $this->url = $url;

        $this->blog_name = $blog_name;

        $this->title = $title;

        $this->excerpt = $excerpt;
    }        

    public function send()
    {
        // $this->send_trackback_ping('http://ping.myblog.jp/', $this->url, $this->title, $this->blog_name); // myblog

        $this->weblogUpdates_ping('http://ping.myblog.jp/');

        // $this->pingWeblogs('ping.myblog.jp', 80, '/'); // myblog

        // send_trackback_ping('http://ping.myblog.jp/', $this->url, $this->title, $this->blog_name);

        $this->weblogUpdates_ping('http://ping.bloggers.jp/rpc/'); // http://ping.bloggers.jp/rpc/

        // $this->post_ping('http://ping.bloggers.jp/rpc/'); // http://ping.bloggers.jp/rpc/

        // $this->send_trackback_ping('http://ping.bloggers.jp/rpc/', $this->url, $this->title, $this->blog_name);

        // $this->pingWeblogs('ping.bloggers.jp',80, '/rpc/', 'euc-jp'); // http://ping.bloggers.jp/rpc/

        // $this->send_trackback_ping('http://bulkfeeds.net/rpc', $this->url, $this->title, $this->blog_name);
        $this->weblogUpdates_ping('http://bulkfeeds.net/rpc', 'euc-jp'); // bulkfeeds
        // $this->pingWeblogs('bulkfeeds.net', 80, '/rpc'); // bulkfeeds
    }

    /*
        function pingWeblogs($host, $port, $path, $encoding = 'UTF-8') {
            // original function by Dries Buytaert for Drupal
            $client = new xmlrpc_client($path, $host, $port);
            $resultFlg = true;
            $message = new xmlrpcmsg(
                "weblogUpdates.ping",
                array(
                    // new xmlrpcval(SimpleBlogUtils::convert_encoding($this->blog_name, _CHARSET, $encoding)),
                    new xmlrpcval($this->blog_name),
                    new xmlrpcval($this->url)
                )
            );
            ob_start();
            print_r($message);
            $log = ob_get_contents();
            ob_end_clean();
            SimpleBlogUtils::log($log);
            // $message->encoding = $encoding;

            $log = 'pingWeblogs('.$host.':'.$port.$path.")\n";


            $result = $client->send($message);
            if (!$result) {
                if($this->DEBUG){
                    $log .= 'pingWeblogs failed['.$client->errno.'] '.$client->errstring." ".$host.':'.$port.$path."\n";
                }
                $resultFlg = false;
            }else if($result->faultCode()) {
                // error_reporting(0);
                if($this->DEBUG)
                    $log .= 'pingWeblogs failed['.$result->faultCode().'] '.$result->faultString()." ".$host.':'.$port.$path."\n";
                    // trigger_error('pingWeblogs failed['.$result->faultCode().'] '.$result->faultString().$message->payload." ".$host.':'.$port.$path, E_USER_ERROR);
                $resultFlg = false;
            }else if($this->DEBUG){
                $log .= "request start  ======================\n";
                $log .= $client->raw_request."\n";
                $log .= "request end    ======================\n";
                $log .= "response start ======================\n";
                $log .= $result->raw_res."\n";
                $log .= "response end   ======================\n";
            }
            $log .= 'pingWeblogs('.$host.':'.$port.$path.") -> ".$resultFlg;

            // SimpleBlogUtils::log($log);
            return $resultFlg;
        }
    */

    public function weblogUpdates_ping($url, $encoding = 'UTF-8')
    {
        $param = [];

        $result = false;

        $title = $this->blog_name;

        $snoopy = new Snoopy();

        // $snoopy->_fp_timeout = $this->$timeout;

        $snoopy->set_submit_xml();

        $xml = '<?xml version="1.0" encoding="' . $encoding . '"?>' . "\n";

        $xml .= "<methodCall>\n";

        $xml .= "<methodName>weblogUpdates.ping</methodName>\n";

        $xml .= "<params>\n";

        $xml .= "<param>\n";

        $xml .= '  <value>' . htmlspecialchars($this->blog_name, ENT_QUOTES | ENT_HTML5) . "</value>\n";

        $xml .= "</param>\n";

        $xml .= "<param>\n";

        $xml .= '  <value>' . htmlspecialchars($this->url, ENT_QUOTES | ENT_HTML5) . "</value>\n";

        $xml .= "</param>\n";

        $xml .= "</params>\n";

        $xml .= "</methodCall>\n";

        $param[0] = SimpleBlogUtils::convert_encoding($xml, _CHARSET, $encoding);

        if ($snoopy->submit($url, $param)) {
            $result = true;
        }

        $log = formatTimestamp(mktime(), 'm');

        $log .= ' start weblogUpdates_ping(' . $url . ")========================\n";

        $log .= $xml . "\n";

        $log .= "====================\n";

        $log .= $snoopy->results . "\n";

        $log .= 'end weblogUpdates_ping(' . $url . ")========================\n";

        SimpleBlogUtils::log($log);

        return $result;
    }

    public function post_ping($url)
    {
        $result = false;

        $param = [];

        $param['url'] = $this->url;

        if (!empty($this->blog_name)) {
            $param['blog_name'] = $this->blog_name;
        }

        $param['title'] = (empty($this->title)) ? $this->url : $this->title;

        $snoopy = new Snoopy();

        $snoopy->_fp_timeout = $this->$timeout;

        $snoopy->set_submit_normal();

        if ($snoopy->submit('http://ping.myblog.jp/', $param)) {
            $result = true;
        }

        if ($this->DEBUG) {
            print 'post_ping(' . $url . ")\n";

            print "==================================>\n";

            print_r($param);

            print "\n";

            print "<==================================\n";

            print $snoopy->results . "\n";
        }

        return $result;
    }    

    public function send_trackback_ping($trackback_url, $url, $title, $blog_name, $excerpt = null)
    {
        $query_string = 'url=' . urlencode($url);

        if (!empty($title)) {
            $query_string .= '&title=' . urlencode($title);
        }

        if (!empty($blog_name)) {
            $query_string .= '&blog_name=' . urlencode($blog_name);
        }

        if (!empty($excerpt)) {
            $query_string .= '&excerpt=' . urlencode($excerpt);
        }

        /*
        if (strstr($trackback_url, '?')) {
            $trackback_url .= "&".$query_string;
            $fp = @fopen($trackback_url, 'r');
            $result = @fread($fp, 4096);
            @fclose($fp);
        } else {
        */

        $trackback_url = parse_url($trackback_url);

        if (!array_key_exists('port', $trackback_url)) {
            $trackback_url['port'] = 80;
        }

        $path = $trackback_url['path'];

        if (array_key_exists('query', $trackback_url)) {
            $path .= '?' . $trackback_url['query'];
        }

        $result = '';

        $http_request = 'POST ' . $path . " HTTP/1.0\r\n";

        $http_request .= 'Host: ' . $trackback_url['host'] . "\r\n";

        $http_request .= 'Content-Type: application/x-www-form-urlencoded' . "\r\n";

        $http_request .= 'Content-Length: ' . mb_strlen($query_string) . "\r\n";

        $http_request .= "\r\n";

        $http_request .= $query_string;

        $errNo = 0;

        $errStr = '';

        $fs = @fsockopen($trackback_url['host'], $trackback_url['port'], $errNo, $errStr, 10);

        @fwrite($fs, $http_request);

        while ($data = @fread($fs, 4096)) {
            $result .= $data;
        }

        @fclose($fs);

        SimpleBlogUtils::log($http_request . "\n\n" . $result);

        //}
        // return $result;
    }
}
