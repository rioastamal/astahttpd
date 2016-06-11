<?php
/**
 * @filesource
 */
 
/**
 * Module for fixing bad Uri, i.e. /folder become /folder/
 * - Created on Fri, 07 Mar 2008 22:24 GMT+7
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
 
final class mod_autofix_uri extends Module {

   /**
    * Class constructor
    */
   public function __construct() {
      parent::__construct();
      $this->modName = 'mod_autofix_uri';
      $this->modVer = '0.1';
      $this->modAuthor = 'Rio Astamal';
      $this->modCycle = 'first_cycle';
      $this->modType = 'URL';
      $this->modPriority = 24;
      $this->modDesc = 'Module for fixing bad Uri';
   }
   
   /**
    * Main method to activate module
    *
    * @return void
    */    
   public function activate() {
      $target =& $this->V_Target;
      
      if (is_dir($target) && !is_file($target)) {
         if (!$this->isThereLastSlash($this->O_HParser->getScriptName())) {
            $host = $this->O_HParser->getHeader('Host');
            $new_uri = 'http://'.$host.$this->O_HParser->getRequestUri()."/";
            $redirect_header = array('Location' => $new_uri);
            
            throw new HttpException(
               "Moved Permanently to $new_uri",
               "301 - Moved Permanently",
               301,
               $redirect_header
            );
         }
      }
   }
   
   /**
    * method to check wheter a path is ended with '/'
    *
    * @param string $url         path/url need to be checked
    * @return boolean
    */
   private function isThereLastSlash($url) {
      if (substr($url, strlen($url)-1, 1) == '/') {
         return true;
      }
      return false;
   }
   
}

?>
