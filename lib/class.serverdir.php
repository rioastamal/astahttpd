<?php
/**
 * @filesource
 */
 
/**
 * Class for manipulating server directory
 * - Created on Fri, 18 Jan 2008 11:13:56 GMT+7
 * - Updated on Thu, 07 Feb 2008 22:43:21 GMT+7
 *
 * @package       astahttpd
 * @subpackage    lib
 * @copyright     Copyright (c) 2008, Rio Astamal
 * @author        Rio Astamal <me@rioastamal.net>
 * @version       0.1
 * @link          http://astahttpd.sourceforge.net/
 * @license       http://opensource.org/licenses/gpl-license.php GNU GPLv3
 * @since         0.1-alpha
 *
 * @property string $dir - directory/path
 */

class ServerDir {
   protected $dir = null;
   
   /**
    * Constructor
    *
    * @param string $dir         directory/path
    */
   public function __construct($dir="/") {
      $this->dir = $dir;
      $this->fixDirectory();
   }
   
   /**
    * method to set/change current working directory
    *
    * @param string $sDir         directory/path
    * @return void
   */
   public function setDir($sDir) {
      $this->dir = $sDir;
      $this->fixDirectory();
   }
   
   /**
    * method to retrieve current working directory
    *
    * @return string
   */
   public function getDir() {
      return $this->dir;
   }

   /**
    * method to fix directory structure if nessecary e.g:
    * - /home/foo/ => /home/foo
    * - /home/foo//bar => /home/foo/bar
    *
    * @return void
   */
   protected function fixDirectory() {
      // remove last slash
      while (substr($this->dir, strlen($this->dir)-1, 1) == '/') {
         $this->dir = substr($this->dir, 0, strlen($this->dir)-1);
      }

      // remove multiple slash to only one slash and prevent the ".."
      $replaceme = array('#[/]?\.\.[/]?#', '#//#');
      $replacer = array('', '/');
      $this->dir = preg_replace($replaceme, $replacer, $this->dir);
   }
   
   /**
    * method to move to parent directory e.g:
    * - /home/foo/bar   => /home/foo
    *
    * @return string
    */
   public function goToParent($include_drive=true) {
      $dsep = DSEP;
      if (!$include_drive) {
        $dsep = '/';
      }
      
      $dirlist = explode($dsep, $this->dir);
      $head = '/';
      if (IS_WINDOWS && $include_drive) {
         $head = array_shift($dirlist).$dsep;
      } else {
        array_shift($dirlist);
      }
      
      array_pop($dirlist);
      $this->dir = $head.implode($dsep, $dirlist);
      $this->fixDirectory();
      return $this->dir;
   }
   
   /**
    * method to get list of directory
    *
    * @param boolean $hidden         include hidden files or not
    * @return array
    */
   public function getDirList($hidden=false) {
      $dirlist = scandir($this->dir);
      // exclude . and ..
      $dirlist = array_slice($dirlist, 2);
      if ($hidden) {
         return $dirlist;
      }
      
      $newdir = array();
      foreach ($dirlist as $list) { // exclude hidden files
         if (substr($list, 0, 1) != '.') {
              $newdir[] = $list;
         }
      }
      return $newdir;
   }
   
   /**
    * static method to get root directory e.g: /foo/bar => /foo
    *   
    * @param string $dir         directory name
    * @return string
   */
   public static function getRootDir($dir) {
      if ($dir == '/') {
         return $dir;
      }
      $dir = explode('/', $dir);
      array_shift($dir);
      return '/'.array_shift($dir);
   }
   
   /**
    * static function to get a file from a directory
    * e.g: /home/foo/bar/file.php/rest/path => /file.php
    *
    * @param string $dir         directory name
    * @param string $base         base directory
    * @param string &$res         rest of the path
    * @return string
    */
   public static function getFile($dir, $base, &$rest = '') {
      if ($dir == '/') {
         return $dir;
      }
      
      if (IS_WINDOWS) {
        $dir = str_replace("/", "\\", $dir);
      }
      //print "__DIR WAS: $dir\n";
      
      $temp = explode(DSEP, $dir);
      array_shift($temp);  // chop first element
      $curfile = '';
      $anyFile = false; // flag if there's any file in URI or not
      
      foreach ($temp as $ind=>$file) {
        $curfile .= DSEP.$file;
        //print "___CHECKING BASE: $base\n";
        //print "___CHECKING FILE: {$curfile}\n";
        // construct $base and $curfile so PHP can check whether
        // is a file or not
        if (is_file($base.$curfile)) {
          HttpServer::liveDebug( "\nCURFILE: {$base}{$curfile} is a file\n");
          $anyFile = true;
          break;
        }
      }
      
      if (!$anyFile) {
        return '';
      }
      
      $pathinfo = explode($curfile, $dir);
      $rest = end($pathinfo);
      return $curfile;
   }
}

?>
