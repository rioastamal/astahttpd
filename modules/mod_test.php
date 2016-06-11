<?php
/**
 * @filesource
 */
 
/**
 * Simple module to demonstrate how to create a module for astahttpd
 * - Created on Kam, 07 Peb 2008 22:33:18 GMT+7
 *
 * @package       astahttpd
 * @subpackage    modules
 * @copyright     Copyright (c) 2008, Rio Astamal
 * @author        Rio Astamal <me@rioastamal.net>
 * @version       0.1 (for astahttpd >= v0.1-beta3)
 * @link          http://astahttpd.sourceforge.net/
 * @license       http://opensource.org/licenses/gpl-license.php GNU GPLv3
 * @since         0.1-beta3
 */
final class mod_test extends Module {
   /**
    * Class Constuctor
    */
   public function __construct() {
      parent::__construct();
      $this->modName = 'mod_test';
      $this->modVer = '0.1';
      $this->modAuthor = 'Rio Astamal';
      $this->modType = 'test';
      $this->modCycle = 'last_cycle';
      $this->modDesc = 'Simple module to demonstrate how to create a module '.
                       'for astahttpd';
   }
   
   /**
    * Main method to activate module. This moduule will add our content to the end
    * of current content
    *
    * @return void
    */     
   public function activate() {
      // we must use reference if we want our new variable pointing to $content
      // in HttpServer Class
      $ourContent =& $this->V_Content;
      // if we just want to get an value from global variable we don't need to
      // use reference
      $server = $this->O_HttpServer;
      
      $myversion = $this->modVer;
      $ourContent .= "<hr /><h2>Hello there, I'm mod_test version $myversion. ".
                     "Your IP is: {$server->getPeerName()}</h2>\n";
      
      // alternative style (did not use new variable for reference)
      /*
      $this->V_Content .= "<hr /><h2>Hello there, I'm mod_test version ".
                         "$myversion. Your IP is: ".
                         "{$O_HttpServer->getPeerName()}</h2>\n";
      */
   }
}

?>
