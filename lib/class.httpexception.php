<?php
/**
 * @filesource
 */
 
/**
 * Custom exception class to catch HTTP error
 * - Created on Sat, 19 Jan 2008 19:47:59 GMT+7
 * - Updated on Sun, 24 Feb 2008 13:14:43 GMT+7
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
 * @property string $title - title of error
 * @property-read array $excHeader - extra header if provided by caller
 */

class HttpException extends Exception {
   protected $title = null;
   private $excHeader = null;
   private $remHeader = null;
   
   /**
    * Constructor, The fourth parameter is available since 0.1-beta2
    *
    * @param-read string $message           error message
    * @param-read string $title             error title
    * @param-read integer $code             error code
    * @param-read array $header             new HTTP headers
    */
   public function __construct($message, $title, $code=500, $header=null, $removeHeader=null) {
      $code = (int) $code;
      if (!$message) {
         switch ($code) {
            case 302:
               $this->title = "302 - Moved Temporarily";
               $this->message = "<h1>302 - Moved Temporarily</h1>";
            break;
            
            case 301:
               $this->title = "301 - Moved Permanently";
               $this->message = "<h1>301 - Moved Permanently</h1>";
            break;
            
            case 304:
               $this->title = '';
               $this->message = '';
            break;
            
            case 403:
               $this->title = "Error 403 - Access Forbidden";
               $this->message = "<h1>Error 403 - Access Forbidden</h1>";
            break;
            
            case 404:
               $this->title = "Error 404 - Not Found";
               $this->message = "<h1>Error 404 - Not Found</h1>";
            break;
            
            default:
               $this->title = "Error 500 - Internal Server Error";
               $this->message = "<h1>Error 500 - Internal Server Error</h1>";
            break;
         }  
      } else {
         $this->title = $title;
         $this->message = "<h1>$title</h1>\n$message\n";
      }
      $this->code = $code;
      $this->excHeader = $header;
      $this->remHeader = $removeHeader;
   }
   
   /**
    * method to get the title error string
    *
    * @return string
    */
   public function getTitle() {
      return $this->title;
   }
   
   /**
    * method to add new header to current HTTP response
    *
    * @param HttpHeaderMaker  $htmaker       HttpHeaderMaker object
    * @return void
    * @since 0.1-beta2
    */
   public function makeHeader($htmaker) {
      if (is_array($this->excHeader)) {
         foreach ($this->excHeader as $header=>$value) {
            $htmaker->addHeader($header, $value);
         }
      }
   }
   
   /**
    * method to remove new header to current HTTP response
    *
    * @param HttpHeaderMaker  $htmaker       HttpHeaderMaker object
    * @return void
    * @since 0.1-RC1
    */   
   public function deleteHeader($htmaker) {
      if ($this->remHeader) {
         if (is_array($this->remHeader)) {
            foreach ($this->remHeader as $header) {
               $htmaker->removeHeader($header);
            }
         } else {
            $htmaker->removeHeader($this->remHeader);
         }
      }
   }
}

?>
