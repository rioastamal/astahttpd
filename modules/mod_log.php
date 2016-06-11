<?php
/**
 * @filesource
 */
 
/**
 * Module for creating standard log file
 * - Created on Thu, 07 Feb 2008 21:59:36 GMT+7
 * - Updated on Mon, 31 Mar 2008 22:16 GMT+7 
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
final class mod_log extends Module {
   /**
    * Class constructor
    */
   public function __construct() {
      parent::__construct();
      $this->modName = 'mod_log';
      $this->modVer = '0.1';
      $this->modAuthor = 'Rio Astamal';
      $this->modType = 'standard';
      $this->modCycle = 'last_cycle';
      $this->modType = 'Logging';
      $this->modPriority = 784;
      $this->modDesc = 'Module for creating standard log file';
   }

   /**
    * Main method to activate module
    *
    * @return void
    */    
   public function activate() {
      $server =& $this->O_HttpServer;
      $htparser =& $this->O_HParser;
      
      $accessConf = HttpServer::getAwsConf('access_log');
      
      // Log object for access_log
      $logger = new Logger($accessConf['file'], $accessConf['max']);
      $logger->setLogData($server->getPeerName());
      $logger->addLogData(date(DATE_RFC2822)); 
      
      $reqtype = $htparser->getRequestType();
      $logger->addLogData('"'.$reqtype);
      $logger->addLogData($htparser->getRequestUri().'"');   
      
      $logger->addLogData($this->O_HMaker->getRespStatus());
      $logger->addLogData(strlen($this->V_Content).' bytes');
      $logger->addLogData("On {$server->getHostName()}");
      $logger->writeLog();          
      
      unset($logger);
   }
}
