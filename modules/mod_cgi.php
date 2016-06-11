<?php
/**
 * @filesource
 */
 
/**
 * Module for providing CGI script processing
 * - Created on Thu, 07 Feb 2008 21:31:13 GMT+7
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
 */
 
final class mod_cgi extends Module {

   /**
    * Class constructor
    */
   public function __construct() {
      parent::__construct();
      $this->modName = 'mod_cgi';
      $this->modVer = '0.1';
      $this->modAuthor = 'Rio Astamal';
      $this->modCycle = 'second_cycle';
      $this->modType = 'Standard';
      $this->modPriority = 4;
      $this->modDesc = 'Module for providing CGI script processing';
   }
   
   /**
    * Main method to activate module
    *
    * @return void
    */    
   public function activate() {
      if (!is_dir($this->V_Target)) {
         $htparser =& $this->O_HParser;
         $htbuilder =& $this->O_HMaker;
         $server =& $this->O_HttpServer;
         $cgi =& $this->O_Cgi;
         $content =& $this->V_Content;
         $cgiHeader =& $this->V_CgiHeader;
         $staticContent =& $this->V_StaticContent;
         $cgiEnv =& $this->V_CgiEnv;
         
         $target = $this->V_Target;
         
         if (strstr($server->getCgiExt(), $htparser->getFileExtension()) || 
            !$htparser->getFileExtension()) {
            
            $staticContent = false; // change the flag
            
            $reqtype = $htparser->getRequestType();
            $executor = $server->setExecuteHandlerFor($htparser->getFileExtension());
            
            if (!is_executable($target) && !$htparser->getFileExtension()) {
               throw new HttpException (
                  "You do not have permission to execute this file.\n",
                  'Error 403 - Access Forbidden',
                  403
               );
            }
                  
            // set all environment variables for this script
            HttpServer::liveDebug("INSIDE MOD_CGI: Value of PATH_INFO IS ".
               $htparser->getPathInfo()."\n");
            
            $cgiEnv['DOCUMENT_ROOT'] = realpath($server->getBaseDir());
            $cgiEnv['GATEWAY_INTERFACE'] = 'CGI/1.1';
            $cgiEnv['PATH'] = $_ENV['PATH'];
            $cgiEnv['PATH_TRANSLATED'] = $htparser->getPathTranslated();
            $cgiEnv['PATH_INFO'] = $htparser->getPathInfo();
            $cgiEnv['QUERY_STRING'] = $htparser->getQueryString();         
            $cgiEnv['REMOTE_ADDR'] = $server->getPeerName();
            $cgiEnv['REQUEST_URI'] = $htparser->getRequestUri();
            $cgiEnv['REQUEST_METHOD'] = $reqtype;
            $cgiEnv['SERVER_SOFTWARE'] = $server->getServerFullName();
            $cgiEnv['SERVER_NAME'] = $server->getHostName();
            $cgiEnv['SERVER_ADDR'] = '127.0.0.1'; // $server->getHost();
            $cgiEnv['SERVER_PORT'] = $server->getListenPort();
            $cgiEnv['SERVER_SIGNATURE'] = $server->getFooter();
            $cgiEnv['SERVER_PROTOCOL'] = 'HTTP/1.1';
            $cgiEnv['SERVER_ADMIN'] = $server->getServerAdmin();         
            $cgiEnv['SCRIPT_FILENAME'] = $target;
            $cgiEnv['SCRIPT_NAME'] = $htparser->getScriptName();
            $cgiEnv['REDIRECT_STATUS'] = '200';
            
            if (IS_WINDOWS) {
               // it's sucks, made me headeach :)
               foreach ($_ENV as $key=>$val) {
                 $cgiEnv[strtoupper($key)] = $val;
               }
            }
                           
            foreach ($htparser->getAllHeader() as $header=>$val) {
               $header = 'HTTP_'.strtoupper(str_replace('-', '_', $header));
               $cgiEnv[$header] = $val;
            }
            
            $postdata = null;
            $proc = null;
            $pipo = null;
            $resp = '';
            $normalProc = true;
            
            // black lists file
            $black_lists = array('wp-cron.php');   // Wordpress wp-cron.php
                     
            if ($reqtype == 'POST') {
               $cgiEnv['CONTENT_LENGTH'] = $htparser->getHeader('Content-Length');
               // something like application/x-www-form-urlencoded
               $cgiEnv['CONTENT_TYPE'] = $htparser->getHeader('Content-Type');
               $postdata = $htparser->getPostData();
               $desc = array(
                  0 => array('pipe', 'r'), // STDIN
                  1 => array('pipe', 'w'), // STDOUT
                  2 => array('pipe', 'w')  // STDERR
               );              
            } else {
               $desc = array( 
                  1 => array('pipe', 'w'), // STDIN
                  2 => array('pipe', 'w')  // STDERR
               );
            }
                     
            $_current = basename($target);
            
            // HttpServer::liveDebug(print_r($cgiEnv, true));
            
            // I DON'T KNOW WHY IF WE PROVIDE ACTUAL QUERY STRING TO wp-cron.php
            // IT BECOME HANG
            /**
             * TODO: FIX hang wp-cron.php
             */         
            if (in_array($_current, $black_lists) != false) {
               HttpServer::liveDebug("__ACTUAL QUERY_STRING: {$cgiEnv['QUERY_STRING']}\n");
               $cgiEnv['QUERY_STRING'] = 'what-happen-to-query-string--i-dont-know-why';
            }
            
            if (IS_WINDOWS) {
               $bypass = array('bypass_shell' => true);
               $proc = proc_open($executor.' '.$target, $desc, $pipo, null, $cgiEnv, $bypass);
            } else {
               $proc = proc_open($executor.' '.$target, $desc, $pipo, null, $cgiEnv);
            }
            var_dump($proc);
            if (!is_resource($proc)) {
               throw new HttpException("proc_open() failure.", "Error 500 - Internal Server Error", 500);
            }
            
            if ($reqtype == 'POST') {
               fwrite($pipo[0], $postdata);
               fclose($pipo[0]);
            }
            
            $buf = '';
                  
            //do {
               // stream_set_blocking($pipo[1], 0);
               
               // var_dump($pipo[1]);
               // $meta = stream_get_meta_data($pipo[1]);
               
               // print_r($meta);            
               $resp = stream_get_contents($pipo[1]);
               
               // stream_set_timeout($pipo[1], 1, 500000);
               // print "__TRYING FOR: ".(++$zero)." times\n";
               
               //$buf = fread($pipo[1], 4096);
               // print "____CURRENT BUF: $buf\n";
               //$resp .= $buf;
               
            //} while ($buf);
            
            fclose($pipo[1]);
            $err = proc_close($proc);
            
            // var_dump($err, $fc);
            unset($proc, $cgiEnv, $pipo, $desc);
            
            $cgiHeader = $htbuilder->getCgiHeader($resp, $content);
            // print "CGI HEADER: $cgiHeader\nCONTENT: $content\n";
         }
      }
   }
}

?>
