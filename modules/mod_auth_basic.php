<?php
/**
 * @filesource
 */
 
/**
 * Module for providing HTTP Basic Authentication
 * - Created on Thu, 07 Feb 2008 20:49:40 GMT+7
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
 
final class mod_auth_basic extends Module {
   /**
    * Class constructor
    */
   public function __construct() {
      parent::__construct();
      $this->modName = 'mod_auth_basic';
      $this->modVer = '0.1';
      $this->modAuthor = 'Rio Astamal';
      $this->modType = 'Security';
      $this->modCycle = 'first_cycle';
      // mod_digest should first priority instead of mod_basic
      $this->modPriority = 16;
      $this->modDesc = 'Module for providing HTTP Basic Authentication';
   }
   
   /**
    * Main method to activate module
    *
    * @return void
    */
   public function activate() {
      $hp =& $this->O_HParser;
      $authConf =& $this->V_AuthConf;
      
      foreach ($authConf as $auth) {
         if ($auth['type'] == 'Basic') {
            // check the the protected resource location
            if ($auth['rtype'] == 'dir') {
               $isMatch = preg_match("@^".$auth['res']."@", 
                           HttpHeaderParser::WinToUnix($hp->getScriptName()));
            } else {
               $isMatch = preg_match("@^".$auth['res']."$@",
                           HttpHeaderParser::WinToUnix($hp->getScriptName()));
            }
            HttpServer::liveDebug("AUTH MATCHING: {$hp->getScriptName()}");
            if ($isMatch) {
               // check Authorization header
               $logindata = 'Basic '.base64_encode($auth['user'].':'.$auth['pass']);
               $userdata = $hp->getHeader('Authorization');
               HttpServer::liveDebug ("AUTHORIZATION: \"$logindata\" == \"$userdata\" ?\n");
               if (!$userdata || ($userdata != $logindata)) {
                  $autheader = array('WWW-Authenticate' => $auth['type'].
                                    " realm=\"{$auth['realm']}\"");
                                                       
               throw new HttpException(
                  "You are not authorized to view this page.",
                  "Error 401 - Unauthorized",
                  401,
                  $autheader
                );
               } else {
                  break;   // the username password is match
               }
            } // isMatch
         }
      } // foreach
   }
   
}

?>
