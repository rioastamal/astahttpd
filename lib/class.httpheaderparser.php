<?php
/**
 * @filesource
 */
 
/**
 * Class for parsing HTTP header request
 * - Created on Sat, 19 Jan 2008 20:47:09 GMT+7
 * - Updated on Sun, 24 Feb 2008 13:16:12 GMT+7 
 *
 * @package       astahttpd
 * @subpackage    lib
 * @copyright     Copyright (c) 2008, Rio Astamal
 * @author        Rio Astamal <c0kr3x@gmail.com>
 * @version       0.1
 * @link          http://astahttpd.sourceforge.net/
 * @license       http://opensource.org/licenses/gpl-license.php GNU GPLv3
 * @since         0.1-alpha
 *
 * @property array $header - array of HTTP client request header
 * @property string $request - first line of request header
 * @property array $indexPage - array of default index page
 * @property string $requestFile - real file location on the server
 * @property string $scriptName - requested file
 * @property string $queryString - string after '?' on the URI
 * @property string $requestUri - client request URI
 * @property string $postData - the posted data if method was POST
 * @property string $pathInfo - string after real file name
 * @property string $pathTranslated - base dir + pathinfo
 *
 */

class HttpHeaderParser {
   /**
    * @var array
    */
   private $header = null;
   /**
    * @var string
    */
   private $request = null;
   /**
    * @var array
    */
   private $indexPage = null;
   /**
    * @var string
    */
   private $requestFile = null;
   /**
    * @var string
    */
   private $scriptName = null;
   /**
    * @var string
    */
   private $queryString = null;
   /**
    * @var string
    */
   private $requestUri = null;
   /**
    * @var string
    */
   private $postData = null;
   /**
    * @var string
    */
   private $pathInfo = null;  // i.e. /foo.php/dummy/bar => pathinfo is /dummy/bar
   /**
    * @var string
    */
   private $pathTranslated = null;  // base dir + pathinfo
   
   /**
    * Constructor
    *
    * @param string $header         client header
    */
   public function __construct($header) {
      $this->postData = "";   
      $this->extractHeader($header);
      $this->requestUri = $this->getRequest(1);
      $this->setScriptName($this->requestUri);
      $this->setQueryString($this->requestUri);
      $this->pathInfo = '';
      $this->pathTranslated = '';
      $this->indexPage = HttpServer::getAwsConf('index_page');
   }
   
   /**
    * method to set/change default index page name
    *
    * @param array $aPage         lists of index page
    * @return void
    */
   public function setIndexPage($aPage) {
      if (!is_array($aPage)) {
         throw new Exception("Parameter for method setIndexPage must be in array");
      }
      $this->indexPage = $aPage;
   }
   
   /**
    * method to get default index page name
    *
    * @param boolean $inArray         returned as string or array
    * @return mixed
    */
   public function getIndexPage($inArray = false) {
      if ($inArray) {
         return $this->indexPage;
      } else {
         return implode(" ", $this->indexPage);
      }
   }
   
   /**
    * Method to extract some values from HTTP header request. The main
    * purpose of this method is to give value to member data:
    * - header,
    * - postData, and
    * - request
    *
    * @param string $header         HTTP header
    * @return void
    */
   private function extractHeader($header) {
      $oriheader = $header;
      $header = reset(explode("\r\n\r\n", trim($header)));
      $header = explode("\r\n", trim($header));
      
      // get postdata
      // $this->postData = end($header);
      // print("FROM EXTRACT".$this->postData);
      // reset($header);
      $temp = explode("\r\n\r\n", $oriheader, 2);
      $this->postData = rtrim(end($temp));
      
      // HttpServer::liveDebug("POST DATA: \n{$this->postData}\n");
      
      $this->request = trim(array_shift($header));
      $result = null;
      foreach ($header as $line) {
         // to prevent value that contains ":" being splited we limit 2 elements
         $temp = explode(":", $line, 2);
         $result[trim($temp[0])] = trim($temp[1]);
      }
      $this->header = $result;
   }
   
   /**
    * method to set/change post data
    *
    * @param string $sData         post data
    * @return void
    */
   public function setPostData($sData) {
      $this->postData = $sData;
   }
   
   /**
    * method to get posted data
    * 
    * @return string
    */
   public function getPostData() {
      // print("POST DATA: ".$this->postData."\n");
      return $this->postData;
   }
   
   /**
    * method to get HTTP request from header (the first line) e.g:
    * - Request: 
    * - "GET / HTTP/1.1"
    * - then
    * - $this->getRequest(2) 
    * - should return HTTP protocol version like "HTTP/1.1"
    * - (each part is separate by space)
    *
    * @param intger $index         : what part of request should be returned
    * @return string
    */
   private function getRequest($index=0) {
      $req = explode(" ", $this->request);
      if (sizeof($req) == 3) {
         return $req[$index];
      } else {
         throw new HttpException(
            "Your browser sent a request that server did not understand.\n",
            'Error 400 - Bad Request',
            400
         );
      }
   }
   
   /**
    * method to get HTTP request type
    *
    * @return string
    */
   public function getRequestType() {
      if ($this->getRequest() == "POST") {
         return "POST";
      } else if ($this->getRequest() == "GET") {
         return "GET";
      } else if ($this->getRequest() == "HEAD") {
         return "HEAD";
      } else {
         throw new HttpException(
            "Request \"{$this->getRequest()}\" is not supported on this server.\n".
            "Supported request type are: GET, POST, and HEAD only.\n",
            'Error 400 - Bad Request',
            400
            );
      }
   }
   
   /**
    * method to set/change HTTP request URI
    *
    * @param string $sUri         URI
    * @return void
    */
   public function setRequestUri($sUri) {
      $this->requestUri = $sUri;
   }
   
   /**
    * method to get HTTP request URI
    * 
    * @return string
    */
   public function getRequestUri() {
      return $this->requestUri;
   }
   
   /**
    * method to set/change URL query string
    *
    * @param string $sQuery         query string/Uri
    * @return void
    */
   public function setQueryString($sQuery) {
      $qMark = $this->extractQuestMark($sQuery);
      if ($qMark) {
         $this->queryString = $qMark[1];
      } else {
         $this->queryString = '';
      }   
   }
   
   /**
    * method to get URL query string
    *
    * @return string
    */
   public function getQueryString() {
      return $this->queryString;
   }
   
   /**
    * Method to set/change script name
    *
    * @param string $sName         : script name/uri
    * @return void     
    */
   public function setScriptName($sName) {
      $qMark = $this->extractQuestMark($sName);
      if ($qMark) {
         $this->scriptName = $qMark[0];
      } else {
         $this->scriptName = $sName;
      }
   }
   
   /**
    * Method to get script name.
    * request Uri, example:
    * Request URI: /foo/bar/dummy.php/another/path
    * Script Name/foo/bar/dummy.php      
    * 
    * @return string
   */
   public function getScriptName() {
      return $this->scriptName;
   }
   
   /**
    * static method to check wheter a path is ended with '/'
    *
    * @param string $url         path/url need to be checked
    * @return boolean
    */
   public static function isThereLastSlash($url) {
      if (substr($url, strlen($url)-1, 1) == '/') {
         return true;
      }
      return false;
   }
   
   /**
    * method to set/change PATH_INFO
    *
    * @param string $sPath         : path info
    * @return void
    */
   public function setPathInfo($sPath) {
      $this->pathInfo = $sPath;
   }
   
   /**
    * method to get PATH_INFO information
    *
    * @return string
    */
   public function getPathInfo() {
      return $this->pathInfo;
   }
   
   /**
    * method to set/change PATH_TRANSLATED, e.g:
    *  DOCUMENT_ROOT: /foo/bar
    * REQUEST_URI: /dummy.php/another/path
    * then
    * PATH_TRANSLATED: /foo/bar/another/path
    *
    * @param string $sPath         : PATH_TRANSLATED
    * @return void
    */
   public function setPathTranslated($sPath) {
      $this->pathTranslated = $sPath;
   }
   
   /**
    * method to get PATH_TRANSLATED
    *
    * @return string
    */
   public function getPathTranslated() {
      return $this->pathTranslated;
   }
   
   /**
    * This is the most important method from HeaderParser class.
    * This method translate request URI to real localtion of the file
    * in server
    * 
    * @param string $basedir         document root/base directory
    * @return string
    */
   public function getRequestFile($basedir) {

      $sdir = new ServerDir($basedir);
      $drive = "";
      
      // request URI without query string, we don't use scriptName since it will modifed
      // later by other part of the script
      $req_no_qs = reset(explode('?', $this->requestUri, 2));
      
      $sfile = new ServerDir(urldecode($req_no_qs));
      $basedir = $sdir->getDir();
      // $file = realpath($file);
      
      // $root = ServerDir::getRootDir($this->getScriptName());
      // $root = ServerDir::getRootDir($this->scriptName);
      // get root directory from REQUEST URI
      $root = ServerDir::getRootDir($sfile->getDir());
      print "___SFILE: $root\n";
      
      // is an alias?
      $i = 0;
      foreach (HttpServer::getAwsConf('alias_dir') as $alias=>$aliasDir) {
         $goToParent = true;
         if ($alias == $root) {  
            $basedir = $aliasDir;
            
            // if alias is empty use server root dir
            if (!$basedir) {
               $sdroot = new ServerDir(AWS_ROOT_DIR);
               $basedir = $sdroot->getDir();
               
               $goToParent = false;
            }

            HttpServer::liveDebug("ALIAS: $alias = ROOT: $root\n");
            $sdir->setDir($basedir); // change the base directory
            
            // if we don't move to parent directory the requested URL
            // will be doubled e.g: /icons/icons
            if ($goToParent) {
               $basedir = $sdir->goToParent();
            }
            break;
         }
      }
      
      // get the real filename not the path info
      $file = ServerDir::getFile($this->scriptName, $basedir, $pathinfo);
      if ($file) {
         $this->setScriptName($file);
         $file = $basedir.$file;

         HttpServer::liveDebug("\nBEFORE: $file\n");
         HttpServer::liveDebug("PATHINFO: $pathinfo\n");
         if ($pathinfo) {
            $this->pathInfo = $pathinfo;
            $this->pathTranslated = $basedir.$pathinfo;
         }
      } else {
         $file = $basedir.$sfile->getDir();
      }
      // $file = $basedir.$this->getScriptName();
      
      
      // $file = realpath($file);
      HttpServer::liveDebug("AFTER: $file\n");
            
      if (!file_exists($file)) {
         throw new HttpException(
            "The requested URL {$this->scriptName} was not found on this server.\n",
            'Error 404 - Not Found',
            404
         );
      }
            
      if (!is_readable($file)) {
         throw new HttpException(
            "You do not have permission to access {$this->getScriptName()} on this server.\n",
            'Error 403 - Access Forbidden',
            403
         );
      }
                  
      // view the index file if they exists in dir
      if (!is_file($file)) {
         foreach ($this->getIndexPage(true) as $indexfile) {
            if (file_exists($file.DSEP.$indexfile)) {
               $file .= DSEP.$indexfile;
               break;
            }
         }
      }
      
      $this->requestFile = $file;
            
      // is the file in our base dir
      if (strpos(realpath($file), $basedir) === false) {
         throw new HttpException(
            "File that you request is outside base directory.\n".
            "BaseDir: $basedir\n".
            "Yours: ".realpath($file)."\n",
            'Error 403 - Access Forbidden',
            403
         );
      }

      if (IS_WINDOWS) {
         $file = self::UnixToWin($file);
      }
      return $file;
   }
   
   /**
    * method to get mime type
    *
    * @return string
    */
   public function getMimeType() {
      $mimelist = HttpServer::getAwsConf('mime_types');
      $ext = strtolower($this->getFileExtension());
      foreach ($mimelist as $mime_ext => $mimetype) {
         if (strpos($mime_ext, $ext) !== false) {
            return $mimetype;
         }
      }
      
      return "application/octet-stream"; // default
   }
   
   /**
    * method to get extension of the requested file
    *
    * @return string
    */
   public function getFileExtension() {
      $path = pathinfo($this->requestFile);
      return $path['extension'];
   }
   
   /**
    * method to get HTTP protocol version request
    *
    * @return string
    */
   public function getRequestVersion() {
      return $this->getRequest(2);
   }
   
   /**
    * method to get a header from HTTP request, e.g:
    * [OTHER_HEADER]\r\n
    * Host: localhost\r\n
    *
    * $object->getHeader('Host'); // return localhost
    *
    * @param string $name         header name
    * @return string
    */
   public function getHeader($name) {
      if (array_key_exists($name, $this->header)) {
         return $this->header[$name];
      } else {
         // throw new Exception("There is no header named \"$name\"\n");
         // trigger_error('There is no header named "'.$name, E_USER_WARNING);
         return '';
      }
   }
   
   /**
    * method to get all HTTP request header
    *
    * @return array
   */
   public function getAllHeader() {
      return $this->header;
   }
   
   /**
    * Method to split request URI based on question mark '?'. This method
    * primary used for getting script name and path info
    * 
    * @param string $uri         Request URI
    * @return mixed
    */
   private function extractQuestMark($uri) {
      if (strstr($uri, '?')) {
         return explode('?', $uri);
      } else {
         return null;
      }
   }
   
   public static function winToUnix($dir) {
      return str_replace("\\", "/", $dir);
   }
   
   public static function UnixToWin($dir) {
      return str_replace("/", "\\", $dir);
   }
}

?>
