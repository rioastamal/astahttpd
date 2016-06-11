<?php
/**
 * @filesource
 */
 
/**
 * Module for compressing output to gzip or deflate format
 * - Created on Thu, 07 Feb 2008 21:59:36 GMT+7
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
 */
final class mod_encoding extends Module {
   /**
    * Class constructor
    */
   public function __construct() {
      parent::__construct();
      $this->modName = 'mod_encoding';
      $this->modVer = '0.1';
      $this->modAuthor = 'Rio Astamal';
      $this->modType = 'standard';
      $this->modCycle = 'last_cycle';
      $this->modType = 'Standard';
      $this->modPriority = 512;
      $this->modDesc = 'Module for compressing output to gzip or deflate format';
   }

   /**
    * Main method to activate module
    *
    * @return void
    */    
   public function activate() {
      if (!is_dir($this->V_Target)) {
      
         if ($this->V_StaticContent) {
            $htbuilder =& $this->O_HMaker;
            $htparser =& $this->O_HParser;
            $content =& $this->V_Content;
            
            if (strlen($content) > 0) {
               $sentheader = $htparser->getHeader('Accept-Encoding');
               if (strpos($sentheader, 'gzip') !== false || strpos($sentheader, 'deflate') !== false) {
                  $gz = new Compressor($content);
                  if (strpos($sentheader, 'deflate') !== false) {
                     $gz->setOutputType('deflate');
                  }
                  
                  // if not null string
                  $temp = $gz->getOutput();
                  if ($temp) {
                     $content = $temp;
                     $htbuilder->addHeader('content-encoding', $gz->getOutputType());
                     $htbuilder->addHeader('content-length', strlen($content), true);
                  }
               }
            } // if strlen
            
         }
         
       } 
   }
}

?>
