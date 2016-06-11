<?php
/**
 * @filesource
 */
 
/**
 * Module for providing live status of the server
 * - Created on Thu, 07 Feb 2008 22:08 GMT+7
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
 
class mod_status extends Module {
   /**
    * Class constructor
    */
   public function __construct() {
      parent::__construct();
      $this->modName = 'mod_status';
      $this->modVer = '0.1';
      $this->modAuthor = 'Rio Astamal';
      $this->modType = 'Standard';
      $this->modCycle = 'last_cycle';
      $this->modPriority = 32;
      $this->modDesc = 'Module for providing live status of the server';
   }
   
   /**
    * Main method to activate module
    *
    * @return void
    */    
   public function activate() {
      $htparser =& $this->O_HParser;
      $hm =& $this->O_HMaker;
      $server =& $this->O_HttpServer;
      $content =& $this->V_Content;
      
      $file = $this->V_BandwidthConf['file'];
      $limit = $this->V_BandwidthConf['limit']."kb";
      $uri = HttpServer::getAwsConf('mod_status');
      $uri = $uri['url_status'];
      
      $sname = $server->getServerFullName();
      // $bandwidth = "<strong style=\"color:red\">N.A/Unlimited</strong>\n";
      if ($limit <= 0) {
         $limit = "Unlimited";
      }
      $size = mod_bandwidth::readBandwidth();
      $bandwidth = "<strong>".round($size/1024, 2)."kb / ".$limit."</strong>\n";
      
      HttpServer::liveDebug("MOD_STATUS: COMPARING {$htparser->getScriptName()} vs $uri\n");
      if ($htparser->getScriptName() == $uri) {
         $hp = new HtmlPage($sname." Status");
         $css = "body {background-color: #ffffff; color: #000000;}
body, td, th, h1, h2 {font-family: sans-serif;}
pre {margin: 0px; font-family: monospace;}
a:link {color: #000099; text-decoration: none; background-color: #ffffff;}
a:hover {text-decoration: underline;}
table {border-collapse: collapse;}
.center {text-align: center;}
.center table { margin-left: auto; margin-right: auto; text-align: left;}
.center th { text-align: center !important; }
td, th { border: 1px solid #000000; font-size: 75%; vertical-align: baseline;}
h1 {font-size: 150%;}
h2 {font-size: 125%;}
.p {text-align: left;}
.e {background-color: #ccccff; font-weight: bold; color: #000000;}
.h {background-color: #9999cc; font-weight: bold; color: #000000;}
.v {background-color: #cccccc; color: #000000;}
.vr {background-color: #cccccc; text-align: right; color: #000000;}
img {float: right; border: 0px;}
hr {width: 600px; background-color: #cccccc; border: 0px; height: 1px; color: #000000;}";

$body = "<div class=\"center\">\n";

$mem   = round(memory_get_usage()/1024, 0);
$startup = gmdate('D, d M Y H:i:s', $this->V_BootTime);
$os = php_uname('s').' '.php_uname('r').' '.php_uname('v');
$body .= "<h2>$sname - Server Status</h2><hr />\n";
$body .= "<table border=\"0\" cellpadding=\"3\" width=\"600\">
<tr class=\"h\"><th>Server Information</th><th>Value</th></tr>
<tr><td class=\"e\">Server Host</td><td class=\"v\">{$server->getHostName()}</td></tr>
<tr><td class=\"e\">Server Port</td><td class=\"v\">{$server->getListenPort()}</td></tr>
<tr><td class=\"e\">Server Time</td><td class=\"v\">{$server->getServerDate()}</td></tr>
<tr><td class=\"e\">Server Started</td><td class=\"v\">$startup GMT</td></tr>
<tr><td class=\"e\">Server Uptime</td><td class=\"v\">{$this->getServerUptime()}</td></tr>
<tr><td class=\"e\">Server OS</td><td class=\"v\">$os</td></tr>
<tr><td class=\"e\">Bandwidth</td><td class=\"v\">$bandwidth</td></tr>
<tr><td class=\"e\">Memory Usage</td><td class=\"v\">$mem kb.</td></tr>
</table>

<hr /><h2>Loaded Modules</h2><hr />

<table border=\"0\" cellpadding=\"3\" width=\"780\">
<tr class=\"h\"><th>Module Name</th><th>Cycle</th><th>Priority</th><th>Type</th>
<th>Version</th><th>Author</th><th>Description</th>
</tr>";
         $tempmod = $this->V_AwsModules;
         ksort($tempmod);
         foreach ($tempmod as $module) {
            $body .= "<tr><td class=\"e\">{$module->getModName()}</td>
                     <td class=\"v center\">{$module->getModCycle()}</td>
                     <td class=\"v center\">{$module->getModPriority()}</td>
                     <td class=\"v center\">{$module->getModType()}</td>
                     <td class=\"v center\">{$module->getModVer()}</td>
                     <td class=\"v center\">{$module->getModAuthor()}</td>
                     <td class=\"v\">{$module->getModDesc()}</td></tr>";
         }

$body .= "</table>Note: lower number means higher priority. Priority is relative to cycle.<hr />";
$body .= "
<h2>About</h2><hr />
<table border=\"0\" cellpadding=\"3\" width=\"600\">
<tr class=\"v\"><td>
<p>
This program is free software; you can redistribute it and/or modify it 
under the terms of the GNU GPLv3. You can see in the file called LICENSE.
</p>
<p>This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
</p>
<p>If you have question, bugs, or anything please feel free to contact me at me@rioastamal.net or go to http://astahttpd.sourceforge.net/.
</p>
</td></tr>
</table><hr />";

$body .= '</div>';

         $hp->setCss($css);
         $hp->setBody($body);
         $content = $hp->buildPage();
         $hm->setRespStatus(200);
         unset($body, $css, $hp);
      }
             
   }
   
   /**
    * method to get server uptime in x days x hours x min. x seconds
    *
    * @return string
    */
   private function getServerUptime() {
      $current = time();
      
      $run = $current - $this->V_BootTime;
      // convert to days
      $days = floor($run / (60 * 60 * 24));
      // get the remaining time
      $remain = $run % (60 * 60 * 24);
      
      $hours = floor($remain / (60 * 60));
      $remain = $remain % (60 * 60);
      
      $minutes = floor($remain / 60);
      $seconds = $remain % 60;
      
      return "$days day(s) $hours hour(s) $minutes min. $seconds sec.";
   }
                
}
