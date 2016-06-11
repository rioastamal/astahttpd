<?php
/**
 * @filesource
 */
 
/**
 * Class for creating HTTP response header
 * - Created on Sat, 19 Jan 2008 19:58 GMT+7
 * - Updated on Tue, 26 Feb 2008 13:11 GMT+7
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
 * @property array $statusCode - HTTP response status
 * @property string $header - sent header
 * @property string $httpVersion - HTTP protocol version
 * @property integer $respStatus - HTTP response status
 *
 */

class HttpHeaderMaker {
   /**
    * @var array
    */
   private $statusCode = null;
   /**
    * @var string
    */
   private $header = null;
   /**
    * @var string
    */
   private $httpVersion = null;
   /**
    * @var integer
    */
   private $respStatus = null;   // HTTP response status
   
   /**
    * Constructor
    *
    * @param int $status         default HTTP response status
    */
   public function __construct($status = 200) {
      // not all HTTP status code covered here
      // this value will sent on HTTP header at first line
      $this->statusCode = array(
         200 => '200 OK',
         301 => '301 Moved Permanently',
         302 => '302 Found',
         304 => '304 Not Modified',
         400 => '400 Bad Request',
         401 => '401 Unauthorized',
         403 => '403 Forbidden',
         404 => '404 Not Found',
         410 => '410 Gone',
         500 => '500 Internal Server Error',
         501 => '501 Not Implemented',
         509 => '509 Bandwidth Limit Exceeded'
         );
      $this->respStatus = $status;
      $this->header = array(array());
      $this->httpVersion = "HTTP/1.1";
   }
   
   /**
    * method to set/change HTTP response status
    *
    * @param integer $status         HTTP status code
    * @return void
    */
   public function setRespStatus($status) {
      $this->respStatus = $status;
   }
   
   /**
    * method to get HTTP response status
    *
    * @return integer
    */
   public function getRespStatus() {
      return $this->respStatus;
   }
   
   /**
    * method to set/change HTTP protocol version
    *
    * @param string $ver         HTTP version
    * @return void
    */
   public function setHttpVersion($ver) {
      $this->httpVersion = $ver;
   }
   
   /**
    * method to get HTTP protocol version
    *
    * @return string
    */
   public function getHttpVersion() {
      return $this->httpVersion;
   }
   
   /**
    * method to add header to the current HTTP response array, i.e.
    * - $object->addHeader('content-type', 'text/plain'); 
    * - will produce something like this on response header:
    * - [OTHER HEADER: OTHER VALUE]\r\n
    * - Content-Type: text/plain\r\n
    * 
    * @param string $headerName         header name
    * @param string $value              header value
    * @return void
    * @since 0.1-beta3  $value become optional and $headerName can be an array
    */
   public function addHeader($headerName, $value, $unique=false) {
      if (is_array($headerName)) {
         foreach ($headerName as $name=>$val) {
            $this->header[strtolower($name)][] = $this->capHeader($name).": $val";
         }
      } else {
         if ($unique) {
            $this->removeHeader($headerName);
         }
         $this->header[strtolower($headerName)][] = $this->capHeader($headerName).": ".$value;
      }
   }
   
   /**
    * method to remove some header from current HTTP response
    *
    * @param headerName $headerName         headername
    * @return void
   */
   public function removeHeader($headerName) {
      // unset the array that have index named $headerName
      unset($this->header[strtolower($headerName)]);
   }
   
   /**
    * method to capitalize header name, e.g:
    * content-length >> become >> Content-Length
    *
    * @param string $header         headername
    * @return string
    */
   private function capHeader($header) {
      if (!strstr($header, "-")) {
         return ucfirst($header);
      }
      
      $header = explode("-", $header);
      for ($i=0; $i<count($header); $i++) {
         $header[$i] = ucfirst($header[$i]);
      }
      return implode('-', $header);
   }
   
   /**
    * method to get header that sent by CGI script 
    * and pass the output body to &$cont
    *
    * @param string $resp          CGI response/output (including the header)
    * @param string &$cont         output body not including header
    * @return void
    */
   public function getCgiHeader($resp, &$cont) {
      $header = explode("\r\n\r\n", ltrim($resp), 2);
      // var_dump($header);
      if (sizeof($header) < 2) {
         $cont = $header[0];
         return "\r\n";
      }
      $cont = $header[1];
      return $header[0]."\r\n\r\n";  // we add it since it splitted by 'explode()'
   }
   
   /**
    * method to
    *
    * @param $sHeader      header name
    * @return boolean
    */
   public function isHeaderPresent($sHeader) {
      if (array_key_exists($sHeader, $this->header)) {
         return true;
      } else {
         return false;
      }
   }
    
   /**
    * method to check whether some header is present on 
    * current CGI response or not
    *
    * @param string $sHeader         header name that we want to search
    * @param string $cgiHeader       CGI header output
    * @return boolean
    * @since 0.1-beta3
    */
   public function isHeaderPresentInCgi($sHeader, $cgiHeader) {
      print ("CHECKING HEADER: $cgiHeader <=> $sHeader\n");   
      // if (preg_match("/$sHeader/i", $sHeader)) {
      if (stripos($cgiHeader, $sHeader) === false) {
         return false;
      } else {
         return true;
      }
   }
   
   /**
    * Method to build complete header. There's some issue when provide header
    * if the content from CGI script. The end of HTTP header should controlled by
    * CGI script not by us, so we would not send double blank line (\r\n\r\n) 
    * at the end of header if the content is dynamic.
    *
    * @param string $crlf         \r\n characters
    * @return string
    */
   public function buildHeader($crlf="") {
      if (!array_key_exists($this->respStatus, $this->statusCode)) {
         $this->respStatus = '501';
      }
      $headerSend = $this->httpVersion." ".$this->statusCode[$this->respStatus]."\r\n";
      // $header = implode("\r\n", $this->header);
      $header = '';
      
      foreach ($this->header as $hname=>$newheader) {
         foreach ($newheader as $hval) {
            $header .= "$hval\r\n";
         }
      }
      
      $header = rtrim($header);
      
      // if the content is static $crlf should \r\n
      $headerSend .= $header.$crlf."\r\n";
      
      return $headerSend;
   }
   
   public function __toString() {
      $res = "[- ";
      foreach ($this->header as $h) {
         $res .= "$h\n";
      }
      $res .= "]";
      return $res;
   }
}

?>
