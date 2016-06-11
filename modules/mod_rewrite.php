<?php
/**
 * @filesource
 */
 
/**
 * Module for providing simple URL rewrite, a stupid clone to the infamous
 * Apache mod_rewrie :)
 * - Created on Thu, 07 Feb 2008 22:03:41 GMT+7
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
 
class mod_rewrite extends Module {
   /**
    * Class constructor
    */
   public function __construct() {
      parent::__construct();
      $this->modName = 'mod_rewrite';
      $this->modVer = '0.1';
      $this->modAuthor = 'Rio Astamal';
      $this->modType = 'URL';
      $this->modCycle = 'init_cycle';
      $this->modPriority = 6; // mod_vhost should executed first
      $this->modDesc = 'Module for providing simple URL rewrite';
   }
   
   /**
    * Main method to activate module
    *
    * @return void
    */       
   public function activate() {
      $server =& $this->O_HttpServer;
      $hp =& $this->O_HParser;
      
      if ($this->V_RewEngineConf) {
         $need_to_write_log = true;
         
         // Log object for rewrite_log
         if (!array_key_exists('mod_log', $this->V_AwsModules)) {
            $need_to_write_log = false;
         }
         
         if ($need_to_write_log) {
            $rewriteConf = HttpServer::getAwsConf('rewrite_log');

            $rlogger = new Logger($rewriteConf['file'], $rewriteConf['max']);
            $rlogger->setLogData(date(DATE_RFC2822));
            $rlogger->addLogData("[client: {$server->getPeerName()}]");
            $rlogger->addLogData("[host: {$server->getHostName()}]{$rlogger->getNewLine()}");
         }
         
         HttpServer::liveDebug("REWRITE ENGINE IS ON\n");
         foreach ($this->V_RewriteConf as $rw) {
            $ur = new UrlRewrite($rw['pattern'], $rw['target'], 
                  $hp, $rw['flag']);
            HttpServer::liveDebug("REWRITE >> COMPARING {$rw['pattern']} vs {$ur->getUri()}");
            
            if ($need_to_write_log) {
               $rlogger->addLogData("Comparing {$rw['pattern']} with {$ur->getUri()}");
            }
            
            if ($newloc = $ur->getNewUrl($server->getHostName())) {
            
               if ($need_to_write_log) {
                  $rlogger->addLogData("MATCH, Translated to: $newloc".
                                    $rlogger->getNewLine());
               }
               
               // check to see if we need to redirect or not
               if (strstr($ur->getFlag(), 'R')) {
                  // add ":portnum" if the port is not default 80
                  $port = "";
                  if ($server->getListenPort() != 80) {
                     $port = ':'.$server->getListenPort();
                  }
                  
                  $http = 'http://'.$server->getHostName().$port.$newloc;
                  // if $newloc already contains protocol use that instead
                  if (strstr($newloc, 'http://')) {
                     $http = $newloc;
                  }
                  HttpServer::liveDebug("2. REDIRECTING TO: $http\n");
                  throw new HttpException(
                     "Moved Temporarily to $http",
                     '302 - Moved Temporarily',
                     302,
                     array('Location' => $http)
                  );
               }
                                                
               // change the script name to new location
               $hp->setScriptName($newloc);
               $hp->setQueryString($newloc);
               HttpServer::liveDebug(" >> MATCH!\n");
               HttpServer::liveDebug("NEW LOCATION IS: $newloc\n");
               // flag check
               if (strstr($ur->getFlag(), 'L')) {
                  break;   // don't process anymore
               }
            } else {
               HttpServer::liveDebug (" >> NOT MATCH!\n");
               if ($need_to_write_log) {
                  $rlogger->addLogData('NOT MATCH'.$rlogger->getNewLine());
               }
            }
         } // foreach
         
         if ($need_to_write_log) {
            $rlogger->writeLog();
         }
         unset($rlogger);
       } // if rewrite_engine
   }
                
}

?>
