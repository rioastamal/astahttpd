<?php
/**
 * @filesource
 */
 
/**
 * Module for providing cache control on static content
 * - Created on Sun, 24 Feb 2008 11:54:46 GMT+7
 * - Updated on Mon, 31 Mar 2008 22:16 GMT+7 
 *
 * @package       astahttpd
 * @subpackage    modules
 * @copyright     Copyright (c) 2008, Rio Astamal
 * @author        Rio Astamal <me@rioastamal.net>
 * @version       0.1 (for astahttpd >= v0.1-beta3)
 * @link          http://astahttpd.sourceforge.net/
 * @license       http://opensource.org/licenses/gpl-license.php GNU GPLv3
 * @since         0.1-RC1
 *
 */
 
final class mod_cache extends Module {
   /**
    * Class constructor
    */
   public function __construct() {
      parent::__construct();
      $this->modName = 'mod_cache';
      $this->modVer = '0.1';
      $this->modAuthor = 'Rio Astamal';
      $this->modType = 'Standard';
      $this->modCycle = 'second_cycle';
      $this->modPriority = 128;
      $this->modDesc = 'Module for providing cache control';
   }
   
   /**
    * Main method to activate module
    *
    * @return void
    */
   public function activate() {
      if (!is_dir($this->V_Target)) {
         $staticContent =& $this->V_StaticContent;
         
         if ($staticContent) {
            $hp =& $this->O_HParser;
            $server =& $this->O_HttpServer;               
            $hm =& $this->O_HMaker;
            $if_mod_since = '';
            $if_none_match = '';
            
            $target = $this->V_Target;
            
            $stat = stat($target); // get array of status
            // modification time
            $modtime = gmdate('D, d M Y H:i:s', $stat['mtime']).' GMT';
            // based on $modtime and size of file
            $etag = hash('crc32', $target.$modtime.$stat['size']);
            $cache_header = array('ETag' => $etag,
                                  'Last-Modified' => $modtime,
                                  'Cache-Control' => 'public');
            // we don't need contet-length and content-type since
            // the client has the copy from it cache
            $exclude_header = array('Content-Length', 'Content-Type');

            // Using 'If-None-Match' header comparision
            $if_none_match = $hp->getHeader('If-None-Match');  // this is ETag
            if ($if_none_match) {
               // if Etag is same then the content is not modified
               
               if ($etag == $if_none_match) {
                  $this->V_Content = '';
                  throw new HttpException("", "", 304, $cache_header, $exclude_header);
               }
            }
            
            // Using 'If-Modified-Since' header comparison
            $if_mod_since = $hp->getHeader('If-Modified-Since');
            if ($if_mod_since) { // if not empty do comparison
               // if current mod time <= from client request
               // send 304 None Modified Response
               $server_cache = $stat['mtime'];
               $client_cache = strtotime($if_mod_since);
               
               if ($client_cache !== false) {
                  if ($server_cache <= $client_cache) {
                     $this->V_Content = '';
                     throw new HttpException("", "", 304, $cache_header, $exclude_header);
                  }
               }
            }
            
            // If we goes here, it means the client did not cache or 
            // this is the first time
            $hm->addHeader('Last-Modified', $modtime);
            $hm->addHeader('ETag', $etag);
            
         }
      }
   }
   
}

?>
