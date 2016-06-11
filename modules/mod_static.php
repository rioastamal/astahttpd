<?php
/**
 * @filesource
 */
 
/**
 * Module for providing CGI script processing
 * - Created on Fri, 07 Mar 2008 18:49 GMT+7
 * - Updated on Mon, 31 Mar 2008 22:16 GMT+7 
 *
 * @package       astahttpd
 * @subpackage    modules
 * @copyright     Copyright (c) 2008, Rio Astamal
 * @author        Rio Astamal <c0kr3x@gmail.com>
 * @version       0.1 (for astahttpd >= v0.1-RC1)
 * @link          http://astahttpd.sourceforge.net/
 * @license       http://opensource.org/licenses/gpl-license.php GNU GPLv3
 * @since         0.1-RC1
 *
 */
 
final class mod_static extends Module {

   /**
    * Class constructor
    */
   public function __construct() {
      parent::__construct();
      $this->modName = 'mod_static';
      $this->modVer = '0.1';
      $this->modAuthor = 'Rio Astamal';
      $this->modCycle = 'first_cycle';
      $this->modType = 'Core';
      $this->modPriority = 32;
      $this->modDesc = 'Module for processing static content';
   }
   
   /**
    * Main method to activate module
    *
    * @return void
    */    
   public function activate() {
      $htparser =& $this->O_HParser;
      $htbuilder =& $this->O_HMaker;
      $server =& $this->O_HttpServer;
      $content =& $this->V_Content;
      $target =& $this->V_Target;
      
      if (is_dir($target)) {
         // we not going to throw this since this condition may be processed again
         // by other module like mod_browse_dir
         $content = "Resource {$htparser->getRequestUri()} is a directory.";
         $htbuilder->addHeader("Content-Type", "text/plain");
      } else {
         if (strpos($server->getCgiExt(), $htparser->getFileExtension()) === false) {
               $content = file_get_contents($target);
               "___STATIC CONTENT: $content\n";
               $htbuilder->addHeader('content-type', $htparser->getMimeType());
         }
      }
      
   }
}

?>
