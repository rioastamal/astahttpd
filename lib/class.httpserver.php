<?php
/**
 * @filesource
 */

include_once(AWS_ROOT_DIR."/lib/class.socket.php");
include_once(AWS_ROOT_DIR."/conf/aws.conf.php");
include_once(AWS_ROOT_DIR."/lib/class.httpexception.php");
include_once(AWS_ROOT_DIR."/lib/class.serverdir.php");
include_once(AWS_ROOT_DIR."/lib/class.htmlpage.php");
include_once(AWS_ROOT_DIR."/lib/class.httpheadermaker.php");
include_once(AWS_ROOT_DIR."/lib/class.httpheaderparser.php");
include_once(AWS_ROOT_DIR."/lib/class.compressor.php");
include_once(AWS_ROOT_DIR."/lib/class.urlrewrite.php");
include_once(AWS_ROOT_DIR."/lib/class.logger.php");
include_once(AWS_ROOT_DIR."/modules/module.php");

/**
 * Class for creating HTTP daemon
 * - Created on Sat, 19 Jan 2008 10:41:32 GMT+7
 * - Updated on Tue, 26 Feb 2008 13:12 GMT+7 
 *
 * @package       astahttpd
 * @subpackage    lib
 * @copyright     Copyright (c) 2008, Rio Astamal
 * @author        Rio Astamal <c0kr3x@gmail.com>
 * @version       0.2
 * @link          http://astahttpd.sourceforge.net/
 * @license       http://opensource.org/licenses/gpl-license.php GNU GPLv3
 * @since         0.1-alpha
 *
 * @property string $basedir - Web server base directory(doc_root)
 * @property integer $footer - Signature
 * @property string $cgiExt - List of cgi extension
 * @property string $action - CGI executor
 * @property integer $serverPid - Process Id
 * @property string $serverName - Name of server
 * @property string $serverRelease - Server release status (deprecated)
 * @property string $serverVersion - Server version number
 * @property string $serverTime - Current server date/time (GMT)
 * @property string $serverAdmin - Admin email address
 * @property string $hostName - Server hostname
 */
class HttpServer extends Socket {
   /**
    * @var string
    */
   private $baseDir = null;
   /**
    * @var string
    */
   private $footer = null;
   /**
    * @var string
    */
   private $cgiExt = null;
   /**
    * @var string
    */
   private $action = null;
   /**
    * @var integer
    */
   private $serverPid = null;
   /**
    * @var string
    */
   private $serverName = null;
   /**
    * @var string
    * @deprecated
    */
   private $serverRelease = null;
   /**
    * @var string
    */
   private $serverVersion = null;
   /**
    * @var string
    */
   private $serverTime = null;
   /**
    * @var string
    */
   private $serverAdmin = null;
   /**
    * @var string
    */
   private $hostName = null;
   
   /**
    * Constructor
    *
    */
   public function __construct() {
      $port = self::getAwsConf('port');
      $host = self::getAwsConf('host');
      
      $this->setListenPort($port);
      $this->setHost($host);
      
      // parent::__construct($port, $host);
      $this->baseDir = $this->checkDir(self::getAwsConf('doc_root'));
      
      /* SERVER INFORMATION */
      $this->serverName = self::getAwsConf('server_name'); // 'astahttpd';
      $this->serverVersion = '0.1-RC1';
      $this->serverRelease = 'RC1';
      $this->serverAdmin   = self::getAwsConf('server_admin');
      $this->hostName      = self::getAwsConf('host_name');
      // useless
      // $this->serverTime = gmdate('D, d M Y H:i:s', time()).' GMT';
      /* END SERVER INFORMATION */
                     
      $this->cgiExt = implode(' ', array_keys(self::getAwsConf('cgi_handler')));
      $this->action = self::getAwsConf('cgi_handler');    

   }
   
   /**
    * method to set cgi extension that need to be parsed by server
    *
    * @param string $sExt         : extension list (separate by spaces)
    * @return void
    */
   public function setCgiExt($sExt) {
      $this->cgiExt = $sExt;
   }
   
   /**
    * method to get cgi extension
    *
    * @return string
    */
   public function getCgiExt() {
      return $this->cgiExt;
   }
   
   /**
    * static method to get server configuration
    * 
    * @param string $confname         : configuration name
    * @return mixed
    */
   public static function getAwsConf($confname) {
      return $GLOBALS['aws_conf'][$confname];
   }
   
   /**
    * method to set executor for cgi script
    * 
    * @param string $scriptExt         : extension of script
    * @return string
    */
   public function setExecuteHandlerFor($scriptExt) {
      foreach ($this->action as $ext => $bin) {
         if (strstr($ext, $scriptExt)) {
            self::liveDebug ("HANDLER FOR $scriptExt IS $bin\n");
            return $bin;
         }
      }
      return "";
   }
   
   /**
    * method to set server name, i.e 'astahttpd'
    *
    * @param string $sName         server name
    * @return void
    */
   public function setServerName($sName) {
      $this->serverName = $sName;
   }
   
   /**
    * method to get server name
    *
    * @return string
    */
   public function getServerName() {
      return $this->serverName;
   }
   
   /**
    * method to get full server name, complete with
    * version and release status
    *
    * @internal start form 0.1-beta3 it only return name/version
    * @return string
    */
   public function getServerFullName() {
      return $this->serverName.'/'.$this->serverVersion;
   }
   
   /**
    * method to set/change server version, i.e'0.1'
    *
    * @param string $sVer         server version
    * @return void
   */
   public function setServerVersion($sVer) {
      $this->serverVersion = $sVer;
   }
   
   /**
    * method to get server version
    *
    * @return string
    */
   public function getServerVersion() {
      return $this->serverVersion;
   }
   
   /**
    * method to set/change server release status, i.e 'beta2'
    * 
    * @deprecated
    * @param sRelease $sRelease         release
    * @return void
    */
   public function setServerRelease($sRelease) {
      $this->serverRelease = $sRelease;
   }
   
   /**
    * method to get server release status
    *
    * @deprecated
    * @return string
    */
   public function getServerRelease() {
      return $this->serverRelease;
   }
   
   /**
    * Method to set/change server host name. Since version 0.1 beta1, 
    * astahttpd support virtual host, we should use this setter/getter 
    * instead of getting host name by function like gethostbyaddr()
    *
    * @param string $sHostName         host name
    * @return void
    */
   public function setHostName($sHostName) {
      $this->hostName = $sHostName;
   }
   
   /**
    * method to get server host name
    * @return string
    */
   public function getHostName() {
      return $this->hostName;
   }
   
   /**
    * methot to set server date/time based on GMT
    *
    * @param integer $time         unix timestamp
    * @return void
    */
   public function setServerDate($time = null) {
      if ($time) {
         $this->serverDate = gmdate('D, d M Y H:i:s', $time).' GMT';
      } else { // default
         $this->serverDate = gmdate('D, d M Y H:i:s', time()).' GMT';
      }
   }
   
   /**
    * method to get server date/time, i.e Mon, 28 Jan 2008 09:17:23 GMT
    *
    * @param boolean $now         use provided time or current time
    * @return string
    */
   public function getServerDate($now=true) {
      if ($now) { // default
         return gmdate('D, d M Y H:i:s', time()).' GMT';
      } else {
         return $this->serverDate;
      }
   }
   
   /**
    * method to set base directory(document_root) for web server
    *
    * @param string $sDir         document root
    * @return void
    */
   public function setBaseDir($sDir) {
      $this->baseDir = $sDir;
   }
   
   /**
    * method to get base directory
    * @return string
    */
   public function getBaseDir() {
      return $this->baseDir;
   }
   
   /**
    * method to set server signature/footer
    *
    * @param string $sFoot         signature
    * @return void
    */
   public function setFooter($sFoot) {
      $this->footer = $sFoot;
   }
   
   /**
    * method to get server signature
    *
    * @return string
    */
   public function getFooter() {
      return $this->footer;
   }
   
   /**
    * method to check whether the given path is directory or not, if not
    * extract the directory name
    *
    * @param string $sDir         directory
    * @return string
    */
   private function checkDir($sDir) {
      if (is_dir($sDir)) {
         return $sDir;
      } else {
         return dirname($sDir);
      }
   }
   
   /**
    * method to set/change server admin email address
    *
    * @param string $sEmail         email address
    * @return void
    */
   public function setServerAdmin($sEmail) {
      $this->serverAdmin = $sEmail;
   }
   
   /**
    * method to get server admin email address
    *
    * @return string
   */
   public function getServerAdmin() {
      return $this->serverAdmin;
   }
   
   /**
    * main method to start the web server daemon
    *
    * @return void
   */
   public function startDaemon() {
      // ------------ load modules --------------------------------//
      global $awsModules;
      $awsModules = array(); $tempMod = array();
      foreach (self::getAwsConf('modules') as $modname=>$status) {
         if ($status == 'On') {
            include_once(AWS_ROOT_DIR.'/modules/'.$modname.'.php');
            // only an instance, not activated yet
            $tmp = new $modname();
            $tempMod[$tmp->getModPriority()][$modname] = $tmp;
         }
      }
      // sort modules based on run priority
      ksort($tempMod);
      // convert to 1 dimensional array
      foreach ($tempMod as $mods) {
         foreach ($mods as $mod) {
            $awsModules[$mod->getModName()] = $mod;
         }
      }
      unset($tempMod);
      // ---------------------------------------------------------//
      
      // let's prepare before listeing... :)
      parent::__construct($this->getListenPort(), $this->getHost());
      print ("{$this->serverName} running at {$this->getHost()} ".
             "port {$this->getListenPort()}...\n");
                  
      // $this->serverPid = '/tmp/aws.'.$this->getHost().'.pid';
      // file_put_contents($this->serverPid, posix_getpid());         
      
      $this->unBlockSocket();      
      $listener = $this->startListen();            
      
      //----- we make it global so the modules can access it ------//
      global $authConf, $bootTime, $httpServer, $cgi;
      global $rewEngineConf, $rewriteConf, $bandwidthConf;      
      
      $httpServer = $this; // $httpServer will be used in modules         
      $bootTime = time();
      $bandwidthConf = self::getAwsConf('bandwidth'); 
      $rewEngineConf = self::getAwsConf('rewrite_engine');
      $rewriteConf = self::getAwsConf('rewrite');
      $authConf = self::getAwsConf('auth');         
      //-----------------------------------------------------------//  
      
      // check base directory, if empty use pwd
      if (!$this->baseDir) {
         $this->baseDir = AWS_ROOT_DIR.DSEP.'htdocs';
         self::liveDebug("Warning: doc_root is empty so i moved to {$this->baseDir}\n");
      }

      // Server signature
      $sFullName = $this->serverName.'/'.$this->serverVersion;
      $signature = self::getAwsConf('server_signature');
      
      $signature = str_replace(array("{SERVER_FULL_NAME}", "{HOST}", 
                                     "{PORT}", "{TIME}"),
                              array($sFullName, $this->hostName, 
                                    $this->getListenPort(), 
                                    $this->getServerDate()),
                              $signature);
      $this->footer = $signature;
      // $cgi->setEnv('SERVER_SIGNATURE', $this->footer);       
      
      $read_set = array($listener);
      $buffer = array();
      $is_alive_before = false;
      $sock_array = array();
      $buf = array();
      $request_arr = array();
              
      while (1) {
      
         // copy the read_set so it will not be modified by socket_select
         $copy_set = $read_set;
         
         if (@socket_select($copy_set, $w=NULL, $e=NULL, NULL) < 1) {
            print ("NO CONNECTION, trying again...\n");
            continue;
         } 
                           
         if (in_array($listener, $copy_set)) {
            // add new client to read_set for furter listening
            $read_set[] = $this->acceptConnection();
            
            self::liveDebug("Connected... (Remote Addr: {$this->getPeerName()})\n");

            // remove the listener from copy
            $key = array_search($listener, $copy_set);
            unset($copy_set[$key]);
         }
         
         foreach ($copy_set as $sock_copy) {
           
            //----- we make it global so the modules can access it ------//
            global $request, $responseHeader, $content, $cgiHeader, $htparser;
            global $htbuilder, $postdata, $logger, $staticContent, $cgiEnv, $target;
            
            $request = "";
            $response = "";
            $content = "";
            $cgiHeader = "";
            $htparser = null;
            $htbuilder = null;
            $postdata = null;
            $logger = null;
            $staticContent = true;
            $cgiEnv = null;
            $target = "";
                     
            // get the data
            // $buf = '';
            $trying = 0;
            
            $sock_num = (int)$sock_copy;            
            $sock_array[$sock_num] = $sock_copy;
            $current_sock = $sock_array[$sock_num];
            
            do {
               $buf[$sock_num] = socket_read($current_sock, AWS_READ_BUFF, AWS_REQUEST_DELIM);
               // socket_recv($sock_copy, $buf, AWS_READ_BUFF, 0);
               self::liveDebug( "Buffer left: ".strlen($buf[$sock_num])."\n" );
               $request_arr[$sock_num] .= $buf[$sock_num];
            } while ( ($trying += AWS_READ_BUFF) === strlen($request_arr[$sock_num]) );
            
            // $request = socket_read($sock_copy, 8192*2);
            $request = $request_arr[$sock_num];
            
            // print_r($request_arr);
            
            if ($request === false || strlen($request) == 0) {
               $key = array_search($sock_copy, $read_set);
               unset($sock_array[$sock_num]);
               unset($buf[$sock_num]);
               unset($request_arr[$sock_num]);
               unset($read_set[$key]);
               // unset($copy_set[$key]);
               self::liveDebug("Client disconnected.\n");
               continue;
            }            
            
            // keep looking for 
            if (strpos($request, "\r\n\r\n") === false) {
               continue;
            }
            
            $rqtype = substr($request, 0, 10);
            if (strpos($request, 'POST') !== false) {
               $trying = 0;
               $post_buf = '';
               do {
                  $buf[$sock_num] = socket_read($current_sock, AWS_READ_BUFF, PHP_BINARY_READ);
                  // socket_recv($sock_copy, $buf, AWS_READ_BUFF, 0);
                  self::liveDebug( "Buffer left: ".strlen($buf[$sock_num])."\n" );
                  $post_buf .= $buf[$sock_num];
               } while ( ($trying += AWS_READ_BUFF) === strlen($post_buf) ); 
               $request .= $post_buf;
            }
            
            if ($request === false || strlen($request) == 0) {
               $key = array_search($sock_copy, $read_set);
               unset($sock_array[$sock_num]);
               unset($buf[$sock_num]);
               unset($request_arr[$sock_num]);
               unset($read_set[$key]);
               // unset($copy_set[$key]);               
               self::liveDebug("Client disconnected.\n");
               continue;               
            }
            
            // if we goes here we assume the data has been successfully read
            $request_arr[$sock_num] = '';
            
            self::liveDebug("Client Request was: \n$request\n\n"); 
            
            try {
               $htparser = new HttpHeaderParser($request);                          
            } catch (Exception $e) { }
            
            try {  // 200 OK      
               $crlf = "\r\n";   // only used when deliver static content
               
               $htbuilder = new HttpHeaderMaker();               
               $htbuilder->addHeader('server', $this->getServerFullName());
               $htbuilder->addHeader('date', $this->getServerDate());
               
               // init cycle
               self::loadModules('init_cycle');
               
               $target = $htparser->getRequestFile($this->baseDir);
               
               // first cycle 
               self::loadModules('first_cycle');            
                          
               $reqtype = $htparser->getRequestType();
               
               self::liveDebug("BASEDIR FROM HTTPSERVER CLASS: {$this->baseDir}\n");
               self::liveDebug("STATUS CODE: {$htbuilder->getRespStatus()}\n");
               
               // auth modules
               // self::loadModules('auth');
               
               self::liveDebug("REQUEST_URI: {$htparser->getRequestUri()}\n");
               $mime = $htparser->getMimeType();
               
               // parser modules
               // self::loadModules('parser');
               self::loadModules('second_cycle');
               
               // by default server will serves this content
               if (!$staticContent) {
                  $crlf = "";
               }
               
               $htbuilder->addHeader('content-length', strlen($content));               
               // load module with lauch type 'after_decode'
               // self::loadModules('after_decode');               
              
            } catch (HttpException $e) {
               if ($e->getCode() != 304) {
                  $hp = new HtmlPage($e->getTitle(), $e->getMessage().$this->footer);               
                  $content = $hp->buildPage();
               }
               
               $htbuilder->setRespStatus($e->getCode());
               $htbuilder->addHeader('content-type', 'text/html', true);
               $htbuilder->addHeader('content-length', strlen($content), true);
               
               $e->makeHeader($htbuilder);
               $e->deleteHeader($htbuilder);
               $cgiHeader = "";    
            } catch (Exception $e) {
               $err = "astahttpd server was unable to complete your request because".
                     " there is internal server error.\nPlease contact the server".
                     " administrator at <a href=\"mailto:{$this->serverAdmin}\">".
                     " {$this->serverAdmin}</a> and inform them about this error.".
                     "\n\nThe error was:
                     {$e->getMessage()}";
                     
               $body = "<h1>Error 500 - Internal Server Error</h1>\n"; 
               $body .= "<span class=\"bold\">".nl2br($err)."</span>";
               $body .= $this->footer;
               
               $hp = new HtmlPage("Error 500 - Internal Server Error", $body);
               $content = $hp->buildPage();  
               
               $htbuilder->setRespStatus(500);
               $htbuilder->addHeader('content-type', 'text/html', true);
               $htbuilder->addHeader('content-length', strlen($content), true);
               $cgiHeader = "";
            }
            
            if ($htparser->getRequestType() == 'HEAD') {
               // only serve the header
               $content = '';
            }
            
            self::loadModules('last_cycle');
            
            self::liveDebug("QUERY_STRING: {$htparser->getQueryString()}\n");
            
            // This keep-alive still buggy when dealing with HTTP upload
            // so it's better not to use it
            
            //$keep_alive = stripos($request, 'keep-alive');
             //  if ($keep_alive === false) {
                  // $key = array_search($sock_copy, $read_set);
                  $htbuilder->addHeader('Connection', 'Close');
                  // unset($read_set[$key]);
                  $key = array_search($sock_copy, $read_set);
                  unset($sock_array[$sock_num]);
                  unset($buf[$sock_num]);
                  unset($request_arr[$sock_num]);
                  unset($read_set[$key]);                  
                  // unset($copy_set[$key]);
                  // print "----> SOCK: $key - $sock_copy\n";
                  self::liveDebug("Client disconnected(no keep-alive).\n");
               //} else {
                 // $htbuilder->addHeader('connection', 'keep-alive');  
                  //$htbuilder->addHeader('keep-alive', 'timeout=15 max=90');
               //}
            
            self::liveDebug("Master ID: $listener\nConnectection ID: $sock_copy\n");
            
            // $this->sendData($responseHeader.$content);
            $responseHeader = $htbuilder->buildHeader($crlf).$cgiHeader;            
            $fullPacket = $responseHeader.$content;
            socket_write($sock_copy, $fullPacket, strlen($fullPacket));
            
            self::liveDebug("\nServer Respose: \r\n$responseHeader");
            
            // back to main configuration not the virtual host
            $bandwidthConf = self::getAwsConf('bandwidth'); 
            $rewEngineConf = self::getAwsConf('rewrite_engine');
            $rewriteConf = self::getAwsConf('rewrite');
            $authConf = self::getAwsConf('auth');
                       
         } // foreach
      } // while (1)
          
      $this->shutdownSocket();
      $this->closeSocket();      
   }
   
   /**
    * method to execute modules based by their modCycle value
    *
    * @param string $cycle   module cycle order
    * @return $void
    */
   private function loadModules($cycle) {
      // this is array that has been sorted, so we just need to activate it
      foreach ($GLOBALS['awsModules'] as $module) {
         // $modcycle = $module->getModCycle();
         if ($module->getModCycle() == $cycle) {
            self::liveDebug("ACTIVATING MODULE {$module->getModName()}...\n");
            $module->activate();
         }
      }
   } // loadModules 
   
   /**
    * method to display current task that astahttpd do (in short: LiveDebug)
    * you can turn it off in configuration by set live_debug = false.
    *
    * @param string $mesg     message to display
    * @return void
    */
   public static function liveDebug($mesg) {
      if (self::getAwsConf('live_debug')) {
         print($mesg);
      }
   }
   
}

?>
