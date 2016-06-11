<?php
/**
 * @filesource
 */

/**
 * This class provide simple URL Rewriting
 * - Created on Thu, 31 Jan 2008 23:14:22 GMT+7
 *
 * @package       astahttpd
 * @subpackage    lib
 * @copyright     Copyright (c) 2008, Rio Astamal
 * @author        Rio Astamal <me@rioastamal.net>
 * @version       0.1
 * @link          http://astahttpd.sourceforge.net/
 * @license       http://opensource.org/licenses/gpl-license.php GNU GPLv3
 * @since         0.1-beta2
 *
 * @property string $pattern - pattern to match
 * @property string $target - URL target
 * @property string $uri - Request URI (w/out question string)
 * @property string $flag - Flag could be L or R or L,R
 * L = Last, R = Redirect
 *
 */

class UrlRewrite {
   /**
    * @var string
    */
   private $pattern = null;
   /**
    * @var string
    */
   private $target = null;
   /**
    * @var string
    */
   private $uri = null;
   /**
    * @var string
    */
   private $flag = null;

   /**
    * Constructor
    *
    * @param string $pat            pattern to match
    * @param string $tar            URL target
    * @param HttpHeaderParser $htparser      HttpHeaderParser Object
    * @param string $flag            Rewrite Flag
    */
   public function __construct($pat, $tar, $htparser, $flag='L') {
      $this->pattern = $pat;
      $this->target = $tar;
      $this->uri = $htparser->getScriptName().$htparser->getPathInfo();
      $this->flag = $flag;
   }

   /**
    * method to set/change URL Pattern to match
    *
    * @param string $sPat     pattern to match
    * @return void
    */
   public function setPattern($sPat) {
      $this->pattern = $sPat;
   }

   /**
    * method to get pattern
    *
    * @return string
    */
   public function getPattern() {
      return $this->pattern;
   }

   /**
    * method to set URL Target
    *
    * @param string $sTar            Target Location
    * @return void
    */
   public function setTarget($sTar) {
      $this->pattern = $sTar;
   }

   /**
    * method to get URL target
    *
    * @return string
    */
   public function getTarget() {
      return $this->target;
   }

   /**
    * method to set/change Request Uri (w/out querystring)
    *
    * @param HttpHeaderParser $h        HttpHeaderParser Object
    * @return void
    */
   public function setUri($h) {
      $this->uri = $h->getScriptName().$h->getPathInfo();
   }

   /**
    * method get Request Uri
    *
    * @return string
    */
   public function getUri() {
      return $this->uri;
   }

   /**
    * method to set/change rewrite flag
    *
    * @param string $sFlag       Rewrite flag rule
    * @return void
    */
   public function setFlag($sFlag) {
      $this->flag = $sFlag;
   }

   /**
    * method to get rewrite flag
    *
    * @return string
    */
   public function getFlag() {
      return $this->flag;
   }

   /**
    * method to get the result
    *
    * @param string $hostname    hostname of the target URI
    * @return string
    */
   public function getNewUrl($hostname="") {
      $rewrite = preg_replace("@{$this->pattern}@",
                              $this->target,
                              $this->uri,
                              -1, // no limit
                              $count
                              );
       HttpServer::liveDebug("\nFLAG WAS: {$this->flag}\n");
       if ($count) {
         return $rewrite;
       } else {
         return '';
       }
   }

   /**
    * method to convert class to string object
    *
    * @return string
    */
   public function __toString() {
      return "[pattern: {$this->pattern}, ".
             "target: {$this->target}, ".
             "uri: {$this->uri}, ".
             "flag: {$this->flag}]";
   }
}