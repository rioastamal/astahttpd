<?php
/**
 * @filesource
 */
 
/**
 * Module for providing name based virtual host
 * - Created on Kam, 07 Peb 2008 22:33:35 GMT+7
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
final class mod_vhost extends Module {
   /**
    * Class constructor
    */
   public function __construct() {
      parent::__construct();
      $this->modName = 'mod_vhost';
      $this->modVer = '0.1';
      $this->modAuthor = 'Rio Astamal';
      $this->modType = 'Standard';
      $this->modCycle = 'init_cycle';
      $this->modPriority = 2;
      $this->modDesc = 'Module for providing name based virtual host';
   }
   
   /**
    * Main method to activate module
    *
    * @return void
    */  
   public function activate() {
      $htparser =& $this->O_HParser;
      $server =& $this->O_HttpServer;
      $cgi =& $this->O_Cgi;
      $cgiEnv =& $this->CgiEnv;
      
      // make references to new virtual host
      $authConf =& $this->V_AuthConf;
      $rewEngineConf =& $this->V_RewEngineConf;
      $rewriteConf =& $this->V_RewriteConf;
      $bandwidthConf =& $this->V_BandwidthConf;
      
      // Name based virtual host check
      $namedVhost = HttpServer::getAwsConf('v_host');
      if ($namedVhost) {
         $reqHost = $htparser->getHeader('Host');
         if (!$reqHost) {
            throw new HttpException(
               "Missing \"Host\" target header.\n",
               "Error 400 - Bad Request",
               400
            );
          }
                  
         // loop to get the doc_root/base dir
         $is_vhost = false;
         foreach ($namedVhost as $vHost) {
            if (strstr($reqHost, $vHost['host_name'])) {
               HttpServer::liveDebug(
                  "VHOST: {$vHost['host_name']} == {$reqHost}\n");
                  
               // now overwrite some data
               $server->setBaseDir($vHost['doc_root']);
               $server->setServerAdmin($vHost['server_admin']);
               $server->setHostName($vHost['host_name']);
               //$cgi->setEnv('SERVER_NAME', $vHost['host_name']);
               //$cgi->setEnv('DOCUMENT_ROOT', $server->getBaseDir());
               //$cgi->setEnv('SERVER_ADMIN', $vHost['server_admin']);
               // $cgiEnv['SERVER_NAME'] = $vHost['host_name'];
               // $cgiEnv['DOCUMENT_ROOT'] = $server->getBaseDir();
               // $cgiEnv['SERVER_ADMIN'] = $vHost['server_admin'];
                              
               $signature = HttpServer::getAwsConf('server_signature');
               // assuming same as main server port if empty
               $port = $vHost['port'] ? $vHost['port'] : $server->getListenPort();
               
               $signature = str_replace(array("{SERVER_FULL_NAME}", "{HOST}", 
                                              "{PORT}", "{TIME}"),
                                        array($server->getServerFullName(), 
                                              $vHost['host_name'], 
                                              $port, 
                                              $server->getServerDate()),
                                        $signature);
               $server->setFooter($signature);
               // $cgi->setEnv('SERVER_SIGNATURE', $server->getFooter());
               // $cgiEnv['SERVER_SIGNATURE'] = $signature;
               
               $authConf = $vHost['auth'];
               $rewEngineConf = $vHost['rewrite_engine'];
               $rewriteConf = $vHost['rewrite'];
               $bandwidthConf = $vHost['bandwidth'];
               $is_vhost = true;
               break;
            }
         } // foreach
         
         // normalize
         if (!$is_vhost) {
         
               // now overwrite some data
               $doc_root = HttpServer::getAwsConf('doc_root');
               $admin = HttpServer::getAwsConf('server_admin');
               $host_name = HttpServer::getAwsConf('host_name');
               
               if (empty($doc_root)) {
                  $doc_root = AWS_ROOT_DIR.DSEP.'htdocs';
               }
               
               $server->setBaseDir($doc_root);
               $server->setServerAdmin($admin);
               $server->setHostName($host_name);
               //$cgi->setEnv('SERVER_NAME', $host_name);
               //$cgi->setEnv('DOCUMENT_ROOT', $doc_root);
               //$cgi->setEnv('SERVER_ADMIN', $admin);
               //$cgiEnv['SERVER_NAME'] = $host_name;
               //$cgiEnv['DOCUMENT_ROOT'] = $doc_root;
               //$cgiEnv['SERVER_ADMIN'] = $admin;
                              
               $signature = HttpServer::getAwsConf('server_signature');
               // assuming same as main server port if empty
               $port = $vHost['port'] ? $vHost['port'] : $server->getListenPort();
               
               $signature = str_replace(array("{SERVER_FULL_NAME}", "{HOST}", 
                                              "{PORT}", "{TIME}"),
                                        array($server->getServerFullName(), 
                                              $host_name, 
                                              $port, 
                                              $server->getServerDate()),
                                        $signature);
               $server->setFooter($signature);
               //$cgi->setEnv('SERVER_SIGNATURE', $server->getFooter());
               //$cgiEnv['SERVER_SIGNATURE'] = $signature;
         
         }
         
      }
   }
                
}

?>
