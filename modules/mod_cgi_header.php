<?php
/**
 * @filesource
 */
 
/**
 * Module for providing CGI script processing (depends on mod_cgi)
 * - Created on Thu, 07 Feb 2008 21:53 GMT+7
 * - Updated on Mon, 31 Mar 2008 22:16 GMT+7
 *
 * @package       astahttpd
 * @subpackage    modules
 * @copyright     Copyright (c) 2008, Rio Astamal
 * @author        Rio Astamal <c0kr3x@gmail.com>
 * @version       0.1 (for astahttpd >= v0.1-beta3)
 * @link          http://astahttpd.sourceforge.net/
 * @license       http://opensource.org/licenses/gpl-license.php GNU GPLv3
 * @since         0.1-beta3
 *
 * @property-read array $httpResponse - HTTP request version & status code
 * @property-read array $header - HTTP header that will be send
 */
class mod_cgi_header extends Module {
   /**
    * @var array
    */
   private $httpResponse = null;
   /**
    * @var array
    */
   private $header = null;
   
   /**
    * Class constructor
    */
   public function __construct() {
      parent::__construct();
      $this->modName = 'mod_cgi_header';
      $this->modVer = '0.1';
      $this->modAuthor = 'Rio Astamal';
      $this->modType = 'Standard';
      $this->modCycle = 'second_cycle';
      // this should be executed after all CGI processing
      // so we give 64 (i think it's quite high)
      $this->modPriority = 64;
      $this->modDesc = 'Module for processing CGI header (depends on mod_cgi)';
      
      $this->header = array();
      $this->httpResponse = array();
   }

   /**
    * Main method to activate module
    *
    * @return void
    */    
   public function activate() {
      if (!is_dir($this->V_Target)) {
         $hp =& $this->O_HParser;
         $hm =& $this->O_HMaker;
         $cgiHeader =& $this->V_CgiHeader;
         $this->header = array();
         $this->httpResponse = array();
         
         // only processing when mod_cgi is active
         if (array_key_exists('mod_cgi', $this->V_AwsModules)) {
            // check if this is dynamic then continue processing otherwise leave it      
            if (!$this->V_StaticContent) {
               $this->extractCgiHeader($cgiHeader);
               $this->getCgiHttpResp($cgiHeader);
               
               // check for:
               // - header with "Status: XXX"
               // - HTTP response "HTTP/1.X XXX XXX..."
               // if they exists, rebuild the HTTP response status code
               if ($this->header['Status']) {
                  if ($this->header['Status'][0] != 200) {
                     $status = $this->header['Status'][0];
                     unset($this->header['Status']);   // we don't need this on header
                     
                     foreach ($this->header as $hname=>$hval) {
                        foreach ($hval as $newval) {
                           $hm->addHeader($hname, $newval);
                        }
                     }
                     throw new HttpException("","", $status);
                  }
               }
               
               if ($this->httpResponse) {
                  if ($this->httpResponse['status_code'] != 200) {                       
                     throw new HttpException("","", 
                        $this->httpResponse['status_code']
                      );
                  }
               }

            }
         }
      }
   }
   
   /**
    * method to extract HTTP header from CGI response
    *
    * @param string $cgiHeader            HTTP header
    * @return void
    */
   private function extractCgiHeader($cgiHeader) {
      // return [Header-Name] => [Header-Value] array
      preg_match_all('@(.*): (.*)@', $cgiHeader, $header);
      if ($header) {
         foreach ($header[1] as $i=>$name) {
            $this->header[$name][] = trim($header[2][$i]);
         }
      }
   }
   
   /**
    * method to get HTTP status code and request version from CGI response
    * 
    * @param string $cgiHeader            HTTP header
    * @return void
    */
   private function getCgiHttpResp($cgiHeader) {
      preg_match('@(HTTP/1.[0-2]) (\d+) \w+@i', $cgiHeader, $http);
      if ($http) {
         // request version i.e. HTTP/1.1
         $this->httpResponse['req_ver'] = $http[1];
         // status code i.e. 200 or 302 or 404 etc
         $this->httpResponse['status_code'] = $http[2];
      }
   }
}

?>
