<?php
/**
 * @filesource
 */
 
/**
 * Module for providing bandwidth limiter to each host
 * - Created on Thu, 07 Feb 2008 21:17:32 GMT+7
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
 
final class mod_bandwidth extends Module {
   /**
    * Class constructor
    */
   public function __construct() {
      parent::__construct();
      $this->modName = 'mod_bandwidth';
      $this->modVer = '0.1';
      $this->modAuthor = 'Rio Astamal';
      $this->modType = 'Bandwidth';
      $this->modCycle = 'first_cycle';
      $this->modPriority = 6;
      $this->modDesc = 'Module for providing bandwidth limiter to each host';
   }
   
   /**
    * Main method to activate module
    *
    * @return void
    */   
   public function activate() {
      $server =& $this->O_HttpServer;
      $curBandwidth = self::readBandwidth();
      HttpServer::liveDebug("CURRENT BANDWITH FOR {$server->getHostName()}: ".
                           (round($curBandwidth/1024, 2))."kb\n");
      $max = $this->V_BandwidthConf['limit'] * 1024;
      if ($curBandwidth > $max && $max > 0) {
         throw new HttpException (
            "Host {$server->getHostName()} has exceeded bandwidth limit.
            If you are user visiting this page you can inform administrator at
            <a href=\"mailto:{$server->getServerAdmin()}\">
            {$server->getServerAdmin()}</a> about this error.<br />
            
            If you are administrator of this domain, please consult to
            your server administrator to increase your bandwidth limit.",
            "Error 509 - Bandwidth Limit Exceeded",
            509
         );
      }
      
      $this->writeBandwidth($curBandwidth);
   }
   
   /**
    * method to read current bandwidth. I made static so other modules/class
    * can access this directly
    *
    * @return integer
    */
   public static function readBandwidth() {
      // since it static we can not use $this
      $f = $GLOBALS['bandwidthConf'];
      touch($f['file']);
      $lines = file($f['file']);
      $date = date('mY');
      $lastbw = 0;
      foreach ($lines as $line) {
         if (preg_match("/$date\-(\d+)/", $line, $m)) {
            $lastbw = (int)rtrim($m[1]);
            unset($m);
            break;
         }
      }
      
      return $lastbw;
   }
   
   /**
    * method to update/write bandwidth
    *
    * @param integer $lastBwt            current bandwidth
    * @return void
    */
   private function writeBandwidth($lastBwt) {
      $date = date('mY');
      $req = strlen($this->V_Request);
      $cont = strlen($this->V_Content);
      
      $lines = file($this->V_BandwidthConf['file']);
      $newlines = array();
      
      foreach ($lines as $i=>$line) {
         if (!preg_match("/$date\-\d+/", $line)) {
            $newlines[] = rtrim($lines[$i])."\n";
         }
      }
      
      // bandwidth = client request + served content
      $bwToWrite = $req + $cont + $lastBwt;
      $newlines[] = "$date-$bwToWrite";
      HttpServer::liveDebug("Writing file to {$this->V_BandwidthConf['file']}...\n");
      file_put_contents($this->V_BandwidthConf['file'], $newlines);
   }
   
}

?>
