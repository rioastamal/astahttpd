<?php
/**
 * @filesource
 */
 
/**
 * Module for providing HTTP Digest Authentication
 * - Created on Thu, 07 Peb 2008 21:10:55 GMT+7
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
 *
 */

final class mod_auth_digest extends Module {
   
   /**
    * Class Constructor
    */
   public function __construct() {
      parent::__construct();
      $this->modName = 'mod_auth_digest';
      $this->modVer = '0.1';
      $this->modAuthor = 'Rio Astamal';
      $this->modType = 'Security';
      $this->modCycle = 'first_cycle';
      $this->modPriority = 10;
      $this->modDesc = 'Module for providing HTTP Digest Authentication';
   }
   
   /**
    * Main method to activate module
    *
    * @return void
    */   
   public function activate() {
      $hp =& $this->O_HParser;
      $server =& $this->O_HttpServer;
      $authConf =& $this->V_AuthConf;
      
      foreach ($authConf as $auth) {
         if ($auth['type'] == 'Digest') {
         
            if ($auth['rtype'] == 'dir') {
               $isMatch = preg_match("@^".$auth['res']."@", 
                           $hp->getScriptName());                           
            } else {
               $isMatch = preg_match("@^".$auth['res']."$@",
                           $hp->getScriptName());
            }
            
            $userdata = $hp->getHeader('Authorization');

            // see RFC 2617 for more details
            // expire in 1 hour or the hour was change
            $nonce=md5(date('H').$server->getPeerName());
            $autheader = array('WWW-Authenticate' =>
                         'Digest realm="'.$auth['realm'].'",'.
                         'qop="auth",'.
                         'nonce="'.$nonce.'",'.
                         'opaque="'.md5($auth['realm']).'"');            
            if ($isMatch) {

               if (!$userdata) {
                  throw new HttpException (
                     "You are not authorized to view this page.",
                     "Error 401 - Unauthorized",
                     401,
                     $autheader
                  );
               } else {
                  // convert digest data to array, i.e [qop] => auth
                  preg_match_all('#(\w+)="?([.\w/@\#\s]+)"?,?#', $userdata, $matches);
                  $digest = array_combine($matches[1], $matches[2]);
                  
                  $A1 = md5($auth['user'].':'.$auth['realm'].':'.$auth['pass']);
                  $A2 = md5($hp->getRequestType().':'.$digest['uri']);
                  $valid = md5($A1.':'.$nonce.':'.$digest['nc'].':'.
                               $digest['cnonce'].':'.$digest['qop'].':'.$A2);
                               
                  HttpServer::liveDebug("DIGEST => $valid : {$digest['response']}\n");
                  if ($digest['response'] != $valid) {
                     throw new HttpException (
                        "You are not authorized to view this page.",
                        "Error 401 - Unauthorized",
                        401,
                        $autheader
                     );                  
                  }
               }
               
            } // isMatch
            
            break;
         }
      } // foreach
      
   } // activate()
   
}

?>
