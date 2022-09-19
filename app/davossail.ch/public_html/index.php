<?php
error_reporting(0);
ini_set('display_errors', 0);

// Allow error reporting with debug mode only
if( isset( $_GET['wpd'] ) )
{
	error_reporting(1);
	ini_set('display_errors', 1);
}

$consent_prefix = $_COOKIE['consent'] ? '__no-consent' : '__consent';

/**
 * IIS Hack
 */
if( isset( $_SERVER['SERVER_SOFTWARE'] ) && stristr( $_SERVER['SERVER_SOFTWARE'], 'IIS' ) )
{
	preg_match( "/Microsoft\-IIS\/([0-9\.]+)/i", $_SERVER['SERVER_SOFTWARE'], $iis_match );
	if( isset( $iis_match[1] ) && $iis_match[1] < 7 )
	{
			$_SERVER["REQUEST_URI"] = $_SERVER['HTTP_X_REWRITE_URL'];
	}
}

$base_path = dirname(__FILE__);
$config = parse_ini_file("$base_path/config.ini");

$sub_domain = $config['sub_domain'];
$sub_directory_domain = $config['sub_directory_domain'];
$static_domain = str_replace('https://', '',  $config['static_domain']);
$static_domain = trim($static_domain, '/');
$static_domain = trim($static_domain, '/');
$mobile_ua_regex = $config['mobile_ua_regex'];
$supportedHeaders = $config['http_headers'];
$mobile_prefix = $config['mobile_prefix'];
$send_content_length = true;
if( isset( $config['no_content_length_ua'] ) && !empty( $config['no_content_length_ua'] ) )
{
	$send_content_length = preg_match($config['no_content_length_ua'], $_SERVER['HTTP_USER_AGENT']) ? false : true;
}


//$_SERVER["REQUEST_URI"] = str_replace($sub_directory_domain,"",$_SERVER["REQUEST_URI"]);

$user_agent_prefix = preg_match($mobile_ua_regex, $_SERVER['HTTP_USER_AGENT']) ? '__mobile' : '__web';
if(stripos($_SERVER['HTTP_USER_AGENT'], 'ipad') !== false) {
        $user_agent_prefix = '__web';
}
if(
        $user_agent_prefix == '__mobile' &&
        stripos($_SERVER['HTTP_USER_AGENT'], 'android') !== false &&
        stripos($_SERVER['HTTP_USER_AGENT'], 'mobile') === false
   ){
        $user_agent_prefix = '__web';
}
$http_client = new WpProxyClient($config, $user_agent_prefix);

function debug( $var )
{
	echo '<pre>';
	print_r($var);
	die('debug');
}
// http://il1.php.net/manual/en/function.parse-ini-string.php#111845
// For PHP versions older than 5.3
function parse_ini_string_m($str) {
    
    if(empty($str)) return false;

    $lines = explode("\n", $str);
    $ret = Array();
    $inside_section = false;

    foreach($lines as $line) {
        
        $line = trim($line);

        if(!$line || $line[0] == "#" || $line[0] == ";") continue;
        
        if($line[0] == "[" && $endIdx = strpos($line, "]")){
            $inside_section = substr($line, 1, $endIdx-1);
            continue;
        }

        if(!strpos($line, '=')) continue;

        $tmp = explode("=", $line, 2);

        $key = rtrim($tmp[0]);
        $value = ltrim($tmp[1]);

		if(preg_match("/^\".*\"$/", $value) || preg_match("/^'.*'$/", $value)) {
			$value = substr($value, 1, strlen($value) - 2);
		}

		$t = preg_match("^\[(.*?)\]^", $key, $matches);
		if(!empty($matches) && isset($matches[0])) {

			$arr_name = preg_replace('#\[(.*?)\]#is', '', $key);

			if(!isset($ret[$arr_name]) || !is_array($ret[$arr_name])) {
				$ret[$arr_name] = array();
			}

			if(isset($matches[1]) && !empty($matches[1])) {
				$ret[$arr_name][$matches[1]] = ltrim( $value );
			} else {
				$ret[$arr_name][] = ltrim( $value );
			}

		} else {
			$ret[trim($tmp[0])] = ltrim( $value );
		}  
    }
    return $ret;
}

$publish = file_exists('publish');

if (/*isset($_GET['__renew']) ||*/ $publish) {

	if( true === $publish )
	{
		@unlink('publish');
	}

	$files = explode("\n", file_get_contents('__wpfiles.txt'));
	foreach($files as $file) {
		if (trim($file) != '') {
			$fpath = str_replace($base_path, '', $file);
			list($junk, $base_dir, ) = explode('/', $fpath);
			if (is_dir($base_dir)) {
				recRemoveDir($base_dir);
			} elseif (is_file($base_dir)) {
				unlink($base_dir);
			} elseif (is_file($file)) {
				unlink($file);
			}
		}
	}
	
	recRemoveDir("__web");
	recRemoveDir("__mobile");
	unlink('__wpfiles.txt');

	if( true !== $publish )
	{
		die('ok');
	}
}




$parsed = parse_url($_SERVER['REQUEST_URI']);
//$resource = $parsed['path'];
$resource = str_replace(" ", "%20", $parsed['path']);

$resource_desc_file = "{$base_path}/{$user_agent_prefix}/__index/{$consent_prefix}/{$resource}";

if (
	$_SERVER['REQUEST_METHOD'] == 'GET'
	&&
	(
		// request with parameters, could be html (direct url: /index.php?sid=3&lang=en&act=canvas&id=123) 
		// or image (thumb: /index.php?sid=3&show=17&w=960&h=320)
		strstr($_SERVER['REQUEST_URI'], '/index.php') !== false
		||
		// - homepage
		(empty($_SERVER['QUERY_STRING']) && $_SERVER['REQUEST_URI'] == '/')	
		// - captcha
		||
		strstr($_SERVER['REQUEST_URI'], '/wforms/captcha') !== false
		//pagination
		||
		strstr($_SERVER['REQUEST_URI'], '?page') !== false
		//blog pagination
		||
		strstr($_SERVER['REQUEST_URI'], '?blog-page') !== false
	)
) {
	if( !empty( $GLOBALS['sub_directory_domain'] ) )
	{
		$_SERVER["REQUEST_URI"] = str_replace($sub_directory_domain,"",$_SERVER["REQUEST_URI"]);
	}
	
	// if homepage is requested
	if (empty($_SERVER['QUERY_STRING']) && $_SERVER['REQUEST_URI'] == '/') {
		$key = 'index.html';
	} else {
		$key = md5($_SERVER['REQUEST_URI']);
	}

	$resource_desc_file = "{$base_path}/{$user_agent_prefix}/__index/{$consent_prefix}/{$key}";
	// if static html file does not exist
	if (! is_file($resource_desc_file)) {

		$response = $http_client->request(str_replace(" ", "%20", $_SERVER['REQUEST_URI']));

		// if response is ok and the requested page is not password protected
		if ($response['status'] == 200 && empty($response['headers']['Proxy'])) {

			if ( strstr($response['headers']['Content-Type'], 'text/html') !== false) {
				$resource_path = "{$base_path}/{$user_agent_prefix}/__htmls/{$consent_prefix}/{$key}";
			} else if ( strstr($response['headers']['Content-Type'], 'image/jpeg') !== false) {
				$resource_path = "{$base_path}/{$user_agent_prefix}/__images/{$consent_prefix}/{$key}";
			} else {
				$resource_path = "{$base_path}/{$user_agent_prefix}/__any/{$consent_prefix}/{$key}";
			}
		
			generateDescriptor($resource_desc_file, $resource_path, $response);
			
			$dir_path = dirname($resource_path);
			if (! is_dir($dir_path)) {
				mkdir ( $dir_path, 0777 , true); 
			}

			// Support subdirectory domain START
			if( !empty( $GLOBALS['sub_directory_domain'] ) )
		    {

				$response['body'] = preg_replace_callback( '/[\s]+src[\s]*=[\s]*[\"|\']([^\"\',;\(\{\}\)]+)[\"|\']/is', 'srccb',  $response['body'] );
				
				$response['body'] = preg_replace_callback( '/(<link[^>]*)href[\s]*=[\s]*[\"|\']?([^\"\']+)[\"|\']?([^>]*[^\/>]*)/is', 'bodycb', $response['body'] );
				
				$response['body'] = str_replace( 'href="/"', 'href="/' . $GLOBALS['sub_directory_domain'] . '"', $response['body'] );
				$response['body'] = str_replace( "href='/'", "href='/" . $GLOBALS['sub_directory_domain'] . "'", $response['body'] );
				$response['body'] = str_replace( "url: '/var/", "url: '/" . $GLOBALS['sub_directory_domain'] . "/var/", $response['body'] );
				$response['body'] = str_replace( 'url: "/', 'url: "/' . $GLOBALS['sub_directory_domain'] . '/', $response['body'] );
				$response['body'] = preg_replace_callback( '/<a id="*"([^>]+)href[\s]*=[\s]*[\"|\']?([^\"\']+)[\"|\']?([^>]*[^\/>]*)*>/is', 'bodycblinks', $response['body'] );

			// Support subdirectory domain END
			}
			
			// to prevent random changing domain in menu links in sites with domain aliases
			$content = str_replace(['href="http://'.$_SERVER['HTTP_HOST'], 'href="//'.$_SERVER['HTTP_HOST']], 'href="', $response['body']);
			// save html to file

			file_put_contents($resource_path, $content);
			//file_put_contents($base_path . '/__wpfiles.txt', $resource_path."\n", FILE_APPEND);
		}
		issueResponse($response, $send_content_length);
		exit();
	}
	$descriptor = parse_ini_file($resource_desc_file);
	
	if (version_compare(phpversion(), '5.3', '<')) {
		$ini_content = file_get_contents($resource_desc_file);
		$descriptor = parse_ini_string_m( $ini_content );
	}
	
	header("Status: 200");
	foreach($descriptor['headers'] as $key => $val) {
		header("$key: $val");
	}
	
	readfile($descriptor['path']);
	exit;

	// if the page is already cached and file with content exists
} elseif ($_SERVER['REQUEST_METHOD'] == 'GET' && is_file( $resource_desc_file )) {
	
	$descriptor = parse_ini_file( $resource_desc_file );
	
	if (version_compare(phpversion(), '5.3', '<')) {
		$ini_content = file_get_contents( $resource_desc_file );
		$descriptor = parse_ini_string_m( $ini_content );
	}

	header("Status: 200");
	foreach($descriptor['headers'] as $key => $val) {
		header("$key: $val");
	}
	
	readfile($descriptor['path']);
	exit;

	// if the page is not cached
} else {
	if( !empty($sub_directory_domain) && !stristr( ".shtml", $_SERVER['REQUEST_URI'] ) )
    {
    	$_SERVER['REQUEST_URI'] = str_replace( $sub_directory_domain . '/', '', $_SERVER['REQUEST_URI']);
    }
	$response = $http_client->request(str_replace( ".shtml", "", $_SERVER['REQUEST_URI'] ));

	$status_code = $response['status'];
	$content_type = $response['headers']['Content-Type'];
	if ($_SERVER['REQUEST_METHOD'] == 'GET' && 
		$status_code != "404" && 
		strstr( $_SERVER['REQUEST_URI'],'search.shtml') === false && 
		strstr($_SERVER['REQUEST_URI'],'search') === false && 
		empty($response['headers']['Proxy'])) 
	{
		$resource_path = str_replace(" ", "%20", urldecode($base_path . $resource));
		if (strpos($content_type, 'text/html') !== false || strpos($content_type, 'text/xml') !== false) {
			$resource_path = "{$base_path}/{$user_agent_prefix}/__htmls/{$consent_prefix}" . $resource;
			$resource_dirname = pathinfo($resource);
			$full_path = "{$base_path}/{$user_agent_prefix}/__htmls/{$consent_prefix}" . $resource_dirname['dirname'];
			if (!is_dir($full_path)) {
				mkdir($full_path,0777,true);

			}
			$resource_desc_file = "{$base_path}/{$user_agent_prefix}/__index/{$consent_prefix}" . $resource;
			generateDescriptor($resource_desc_file, $resource_path, $response);
		} else {
			$dir_path = dirname($resource_path);
			if (!is_dir($dir_path)) {
				mkdir ( $dir_path, 0777 , true); 
			}
		}
		file_put_contents($base_path . '/__wpfiles.txt', $resource_path."\n", FILE_APPEND);

		// Support subdirectory domain START
		if( !empty( $GLOBALS['sub_directory_domain'] ) )
		{

			$response['body'] = preg_replace_callback( '/[\s]+src[\s]*=[\s]*[\"|\']([^\"\',;\(\{\}\)]+)[\"|\']/is', 'srccb', $response['body'] );
			
			$response['body'] = preg_replace_callback( '/(<link[^>]*)href[\s]*=[\s]*[\"|\']?([^\"\']+)[\"|\']?([^>]*[^\/>]*)/is', 'bodycb', $response['body'] );
			
			$response['body'] = str_replace( 'href="/"', 'href="/' . $GLOBALS['sub_directory_domain'] . '"', $response['body'] );
			$response['body'] = str_replace( "href='/'", "href='/" . $GLOBALS['sub_directory_domain'] . "'", $response['body'] );
			$response['body'] = str_replace( "url: '/var/", "url: '/" . $GLOBALS['sub_directory_domain'] . "/var/", $response['body'] );
			$response['body'] = str_replace( 'url: "/', 'url: "/' . $GLOBALS['sub_directory_domain'] . '/', $response['body'] );
			$response['body'] = preg_replace_callback( '/<a id="*"([^>]+)href[\s]*=[\s]*[\"|\']?([^\"\']+)[\"|\']?([^>]*[^\/>]*)*>/is', 'bodycblinks', $response['body'] );
			// Support subdirectory domain END
		}

		// to prevent random changing domain in menu links in sites with domain aliases
		$content = str_replace(['href="http://'.$_SERVER['HTTP_HOST'], 'href="//'.$_SERVER['HTTP_HOST']], 'href="', $response['body']);
		file_put_contents($resource_path, $response['body']);
	}
	//echo '<pre>'; print_r($response); exit;
	issueResponse($response, $send_content_length);
	exit;
}
exit;

function bodycblinks( $code )
{
	return '<a id="'.$code[1].'" href="/' . $GLOBALS['sub_directory_domain'] . $code[2] . '">';
}
function srccb( $code )
{
	if (!stristr($code[1], 'http://') && !stristr($code[1], 'https://') && !stristr($code[1], '//')) 
	{
		return ' src="/' . $GLOBALS['sub_directory_domain'] . $code[1] . '"';
	}

	return ' src="' . $code[1] . '"';
}

function bodycb( $code )
{
	if (!stristr($code[1], 'http://') && !stristr($code[1], 'https://') ) 
	{
		return $code[1] . ' href="/' . $GLOBALS['sub_directory_domain'] . $code[2] . '"' . $code[3];
	} 

	return $code[1] . ' href="' . $code[2] . '"' . $code[3];
}

function issueResponse($response,$send_content_length = true) {
	global $supportedHeaders;
	header("Status: " . $response['status']);
	foreach($supportedHeaders as $header) {
		if (isset($response['headers'][$header])) {
			header($header.": " . $response['headers'][$header]);
		}
	}
	$body = $response['body'];
	if( $send_content_length === true )
	{		
		header('Content-Length: ' . strlen($body));
	}
	
	echo $body;
}
function generateDescriptor($descriptor_path, $resource_path, $response) {
	global $supportedHeaders;
	$descriptor = 'path = "' . $resource_path . '"' . "\n";
	
	foreach($supportedHeaders as $header) {
		if (isset($response['headers'][ $header ])) {
			$descriptor .= 'headers[' . $header . '] = "' . $response['headers'][$header] . '"' . "\n";
		}
	}
	$dir_path = dirname($descriptor_path);
	if (! is_dir($dir_path)) {
		mkdir ( $dir_path, 0777 , true); 
	}
	file_put_contents($descriptor_path, $descriptor);
}
function recRemoveDir($path) {
	if (empty($path)) {
		return;
	}
	if (! @is_dir($path)) {
		if (@is_file($path)) { 
			@unlink($path); 
		}
		return;
	}
	$dir = opendir($path);
	while (false !== ($entry=readdir($dir))) {
		if (($entry==".")||($entry=="..")) { continue; }
		if ((@is_file($path."/".$entry))||(@is_link($path."/".$entry))) {
			@unlink($path."/".$entry); 
		} elseif (@is_dir($path."/".$entry)) {
			recRemoveDir($path."/".$entry); 
		}
	}
	closedir($dir);
	$retval = rmdir($path);
}
 
class WpProxyClient
{
	private $sub_domain;
	private $sub_directory_domain;
	private $mobile_prefix;
	private $headers;
	private $cookie;
	
	static $assets_urls = array(
		'/var/','/templates/'
	);
	
	public function __construct($config, $ua)
	{
		$this->sub_domain = $config['sub_domain'];
		$this->static_domain = str_replace('https://', '',  $config['static_domain']);
		$this->static_domain = trim($this->static_domain, '/');
		$this->static_domain = trim($this->static_domain, '/');
		$this->mobile_prefix = $config['mobile_prefix'];
		$this->sub_directory_domain = $config['sub_directory_domain'];
		
		$user_agent = ( $ua == '__mobile' ) ? 'staticexportmobile' : 'staticexportdesktop';
		
		$this->headers = array(
			'User-Agent' => $user_agent,
			'WP-Request-Source' => 'WP_PROXY'
		);
		if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
			$this->headers['X-Requested-With'] = $_SERVER['HTTP_X_REQUESTED_WITH'];
		}
		
		$this->headers['LIVE_DOMAIN'] = $_SERVER['HTTP_HOST'];

		if( !empty( $this->sub_directory_domain ) )
		{
			$this->headers['LIVE_DOMAIN'] = $_SERVER['HTTP_HOST'] . '/' . $this->sub_directory_domain;
		}
	}
	
	public function request($resource)
	{
		$resource = str_replace(" ", "%20", $resource);
		$host = $this->sub_domain;
		foreach( self::$assets_urls as $u )
		{
			if( strpos( strtolower( $resource ) , $u ) !== false ) 
			{
				$host = $this->static_domain;
			}	
		}
		$cookie = [];

		if($_SERVER['REQUEST_METHOD'] == 'POST' ||
			($_SERVER['REQUEST_METHOD'] == 'GET' && strpos($_SERVER["HTTP_ACCEPT"], "text/html") !== false )
		){
			session_start();
			$cookie = isset($_SESSION['WP_COOKIE']) ? $_SESSION['WP_COOKIE'] : [];
		}
		if (isset($_COOKIE['policy']))
			$cookie['policy'] = $_COOKIE['policy'];
		if (isset($_COOKIE['consent']))
			$cookie['consent'] = $_COOKIE['consent'];

		$response = $this->_request($_SERVER['REQUEST_METHOD'], $host, 80, $resource, $_GET, $_POST, $cookie, $this->headers);
		if (isset($response['headers']['Set-Cookie'])){
			$_SESSION['WP_COOKIE'] = isset($_SESSION['WP_COOKIE']) && is_array($_SESSION['WP_COOKIE']) ? array_merge($_SESSION['WP_COOKIE'], $response['headers']['Set-Cookie']) : $response['headers']['Set-Cookie'];
		}
		
		if ($response['status'] == 301 || $response['status'] == 302) {
			$url = str_replace(array('http://', 'https://'), '', $response['headers']['Location']);
			$parsed = parse_url($response['headers']['Location']);
			
			if ($parsed['host'] == $this->sub_domain) {
				header("Location: ".$parsed['scheme']."://" . $_SERVER['HTTP_HOST'] . $parsed['path']);
				exit;
			}
			// Currently redirected using VHOST container APACHE rewrite rules
			/* else if ($parsed['host'] == $this->mobile_prefix . '.' . $this->sub_domain) {
				header("Location: ".$parsed['scheme']."://" . $this->mobile_prefix . "." . $_SERVER['HTTP_HOST'] . $parsed['path']);
				exit;
			}
			*/
			header("Location: ".$parsed['scheme'] . "://" . $parsed['host']);
			exit();
			
			return $this->request($resource);
		}

		$decode = gzdecode($response['body']);
		if($decode !== false){
			$response['body'] = gzdecode($response['body']);
		}
		
		return $response;
	}
	
	private function _request( 
		$verb = 'GET',             /* HTTP Request Method (GET and POST supported) */ 
		$ip,                       /* Target IP/Hostname */ 
		$port = 80,                /* Target TCP port */ 
		$uri = '/',                /* Target URI */ 
		$getdata = array(),        /* HTTP GET Data ie. array('var1' => 'val1', 'var2' => 'val2') */ 
		$postdata = array(),       /* HTTP POST Data ie. array('var1' => 'val1', 'var2' => 'val2') */ 
		$cookie = array(),         /* HTTP Cookie Data ie. array('var1' => 'val1', 'var2' => 'val2') */ 
		$custom_headers = array(), /* Custom HTTP headers ie. array('Referer: http://localhost/ */ 
		$timeout = 30
		) 
	{ 
		$ret = ''; 
		$verb = strtoupper($verb); 
		$cookie_str = ''; 
		$getdata_str = "";
		$postdata_str = ''; 
		$boundary = md5(time());
		$bpre = '--';
		$crlf = "\r\n";
		
		if( empty($_SERVER['QUERY_STRING']) )
		{
			$getdata_str = count($getdata) ? '?' : ''; 
			foreach ($getdata as $k => $v) 
					$getdata_str .= urlencode($k) .'='. urlencode($v) . '&'; 
		}

		foreach ($postdata as $k => $v)
        {
            if( is_array( $v ) )
            {
                foreach( $v as $ik => $iv )
                {
                        $postdata_str .= $bpre . $boundary . $crlf;
                        $postdata_str .= 'Content-Disposition: form-data; name="' . $k . '[' . $ik . ']"' . $crlf . $crlf;
                        $postdata_str .= $iv . $crlf;
                }
            }
            else
            {
                $postdata_str .= $bpre . $boundary . $crlf;
                $postdata_str .= 'Content-Disposition: form-data; name="' . $k . '"' . $crlf . $crlf;
                $postdata_str .= $v . $crlf;
            }
        }

        $postdata_str .= $bpre . $boundary . $crlf;

		if( !empty( $_FILES ) )
        {
            foreach( $_FILES as $fk => $file )
            {
                $postdata_str .=  "Content-Disposition: form-data; name=\"{$fk}\"; filename=\"{$file['name']}\"\n";
                $postdata_str .= "Content-Type: {$file['type']}\n";
                $postdata_str .= "Content-Transfer-Encoding: binary\n\n";
		if (! empty($file['tmp_name'])) {
			$postdata_str .= file_get_contents($file['tmp_name']) . "\n";
		}                
                $postdata_str .= $bpre . $boundary . $crlf . $crlf;
            }
        }

		foreach ($cookie as $k => $v) {
			if (!in_array($k, array('path', 'expires'))) {
				$cookie_str .= urlencode($k) .'='. urlencode($v) .'; ';
			}
		}		

		$req = $verb .' '. $uri . $getdata_str .' HTTP/1.1' . $crlf; 
		$req .= 'Host: '. $ip . $crlf; 
		$req .= 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8' . $crlf; 
		$req .= 'Accept-Language: en-us,en;q=0.8' . $crlf; 
		$req .= 'Accept-Encoding: identity' . $crlf; 
		//$req .= 'Accept-Encoding: gzip, deflate' . $crlf; 
		$req .= 'Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7' . $crlf; 
		
		foreach ($custom_headers as $k => $v) 
			$req .= $k .': '. $v . $crlf; 
			
		if (!empty($cookie_str)) 
			$req .= 'Cookie: '. substr($cookie_str, 0, -2) . $crlf; 
			
		if ($verb == 'POST' && !empty($postdata_str))
        {
            $postdata_str = substr($postdata_str, 0, -1);
            $req .= 'Content-Type: multipart/form-data; boundary=' . $boundary . $crlf;
            $req .= 'Content-Length: '. strlen($postdata_str) . $crlf . $crlf;
            $req .= $postdata_str;
        }
        else $req .= $crlf;

		
		if (($fp = @fsockopen($ip, $port, $errno, $errstr)) == false) 
			die("Error $errno: $errstr ($ip)\n"); 
		
        stream_set_timeout($fp, $timeout);
		
		fputs($fp, $req); 
        while ($line = fgets($fp)) $ret .= $line;
	    $info = stream_get_meta_data($fp);
	    fclose($fp);
	 
	    if ($info['timed_out']) {
	        die("Connection timed out!\n");
	    }
		
		return $this->_parse($ret);
	} 

	private function _parse($response) {
		$retVal = array(
			'status'  => intval(trim(substr($response,9,4))),
			'headers' => array(),
			'body'    => '',
			'content' => ''
		);
		
		$headers = substr($response, 0, strpos($response, "\r\n\r\n"));
		$body = substr($response, strpos($response, "\r\n\r\n") + 4);
		
		$fields = explode("\r\n", preg_replace('/\x0D\x0A[\x09\x20]+/', ' ', $headers));
		foreach ($fields as $field) {
			if (preg_match('/([^:]+): (.+)/m', $field, $match)) {
				$key = preg_replace('/(?<=^|[\x09\x20\x2D])./e', 'strtoupper("\0")', strtolower(trim($match[1])));
				if (empty($key))
					$key = $match[1];
				if (isset($retVal[$match[1]])) {
					$retVal['headers'][$key] = array($retVal[$key], $match[2]);
				} else {
					$retVal['headers'][$key] = trim($match[2]);
				}
			}
		}
		if (isset($retVal['headers']['Set-Cookie'])) {
			$cookies = array();
			$arr = explode('; ', $retVal['headers']['Set-Cookie']);
			foreach($arr as $cookie) {
				list($k, $v) = explode('=', $cookie);
				$cookies[$k] = $v;
			}
			$retVal['headers']['Set-Cookie'] = $cookies;
		}
		$retVal['content'] = $retVal['body'] = $body;
		if (isset($retVal['headers']['Transfer-Encoding']) && $retVal['headers']['Transfer-Encoding'] == 'chunked') {
			$decBody = '';
			
			// If mbstring overloads substr and strlen functions, we have to
			// override its internal encoding
			if (function_exists('mb_internal_encoding') &&
			   ((int) ini_get('mbstring.func_overload')) & 2) {

				$mbIntEnc = mb_internal_encoding();
				mb_internal_encoding('ASCII');
			}

			while (trim($body)) {
				if (! preg_match("/^([\da-fA-F]+)[^\r\n]*\r\n/sm", $body, $m)) {
					break;
				}
				$length = hexdec(trim($m[1]));
				$cut = strlen($m[0]);
				$decBody .= substr($body, $cut, $length);
				$body = substr($body, $cut + $length + 2);
			}

			if (isset($mbIntEnc)) {
				mb_internal_encoding($mbIntEnc);
			}
			
			$retVal['content'] = $decBody;
			$retVal['body'] = $decBody;
			unset($retVal['headers']['Transfer-Encoding']);
		}
		return $retVal;
	}
}
