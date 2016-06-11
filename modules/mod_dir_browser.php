<?php
/**
 * @filesource
 */

include_once(AWS_ROOT_DIR."/lib/class.formattedserverdir.php");

/**
 * Module for browsing content of directory
 * - Created on Fri, 07 Mar 2008 21:43 GMT+7
 * - Updated on Mon, 31 Mar 2008 22:16 GMT+7 
 *
 * @package       astahttpd
 * @subpackage    modules
 * @copyright     Copyright (c) 2008, Rio Astamal
 * @author        Rio Astamal <me@rioastamal.net>
 * @version       0.1 (for astahttpd >= v0.1-RC1)
 * @link          http://astahttpd.sourceforge.net/
 * @license       http://opensource.org/licenses/gpl-license.php GNU GPLv3
 * @since         0.1-RC1
 *
 */

final class mod_dir_browser extends Module {

   /**
    * Class constructor
    */
   public function __construct() {
      parent::__construct();
      $this->modName = 'mod_dir_browser';
      $this->modVer = '0.1';
      $this->modAuthor = 'Rio Astamal';
      $this->modCycle = 'first_cycle';
      $this->modType = 'Standard';
      $this->modPriority = 28;
      $this->modDesc = 'Module for browsing content of directory';
   }
   
   /**
    * Main method to activate module
    *
    * @return void
    */    
   public function activate() {
      $htparser =& $this->O_HParser;
      $target = $this->V_Target;
      
      if (is_dir($target)) {
         // throw to prevent more processing
         $sd = new FormattedServerDir($target);
         throw new HttpException(
            $sd->getFormattedDir($htparser->getScriptName()),
            "Index of {$htparser->getScriptName()}",
            200
         ); // actually no error*/         
      }
   }
   
}
