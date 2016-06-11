<?php
/**
 * Class for creating list of directory in HTML format
 * - Created on Fri, 07 Mar 2008 22:09 GMT+7
 *
 * @package       astahttpd
 * @subpackage    lib
 * @copyright     Copyright (c) 2008, Rio Astamal
 * @author        Rio Astamal <me@rioastamal.net>
 * @version       0.1
 * @link          http://astahttpd.sourceforge.net/
 * @license       http://opensource.org/licenses/gpl-license.php GNU GPLv3
 * @since         0.1-RC1
 */
class FormattedServerDir extends ServerDir {

   /**
    * Constructor
    *
    * @param string $dir      directory that we want to browse
    */
   public function __construct($dir="/") {
      parent::__construct($dir);
   }

   /**
    * Method to format directory in HTML
    *
    * @param string $req      query string/script name
    * @return string
    */
   public function getFormattedDir($req='/') {
      // clear the cache
      clearstatcache();
      // $sd = new ServerDir($this->dir);
      // $lists = array_slice(scandir($this->dir), 2);
      $lists = $this->getDirList();
      $reldir = "";
      $filetitle = "";
      $parentlink = "";

      HttpServer::liveDebug("############## REQUEST ---- WAS: $req\n");
      // $result = "<h1>{$this->message}</h1>\n";
      $result = "<pre>\n";

         $filetitle .= sprintf (
                  "%-50.50s%23.23s%18.18s\n",
                  'Name', 'Last Modified', 'Size');
         $parentlink .= '<hr />';
         $parentlink .= '<img style="position:relative;top:5px;" alt="[parent] " '.
                    'src="/icons/parent.png" />';

         if ($req == '/') {
            $parent = "javascript:alert('Top of Document Root!');";
         } else {
            $sd = new ServerDir($req);
            $parent = $sd->goToParent(false);
            print "___PARENT: $parent\n";

            if ($parent != '/') {
               $parent .= '/';
            }
            HttpServer::liveDebug("############# SD->GETDIR: ".$sd->getDir()."\n");
         }
         $parentlink .= '<a href="'.$parent.'" />Parent Directory</a>'."\n";

      if (sizeof($lists) > 0) {

         $result .= $filetitle . $parentlink;

         foreach ($lists as $list) {
            // do not include hidden file
            if (substr($list, 0, 1) != '.') {
               $curfile = $this->dir.'/'.$list;
               $stat = stat($curfile); // get array of status
               // modification time
               $modtime = strftime('%d-%b-%Y %H:%M', $stat['mtime']);

               $size = $stat['size'] / 1024; // kb
               $kbmb = 'k';
               if ($size > 1024) {  // make it mb
                  $size = $size / 1024;
                  $kbmb = 'M';
               }
               $size = sprintf('%.2f', $size).$kbmb;

               // get extension to determine image that we want to show

               $path = pathinfo($curfile);
               $ext = strtolower($path['extension']);
               $imgfile = "unknown.png";
               if ($ext) {
                  if (strstr("php py cgi html htm", $ext)) {
                     $imgfile = "html.png";
                  } else if (strstr("txt css js", $ext)) {
                     $imgfile = "text.png";
                  } else if (strstr("jpg png gif bmp jpeg", $ext)) {
                     $imgfile = "image.png";
                  } else if (strstr("tar gz bz2 zip", $ext)) {
                     $imgfile = "archive.png";
                  }
               }

               $img = '<img style="position:relative;top:5px;" '.
                      'src="/icons/'.$imgfile.'" alt="[file] " />';

               if (is_dir($curfile)) {
                  $list .= '/';
                  $size = '-';
                  $img = '<img style="position:relative;top:5px;" '.
                         'src="/icons/folder.png" alt="[dir] " />';
               }
               $link = $img.' <a href="'.$list.'">'.$list.'</a>';
               // need some tricky to formatting :)
               $format = sprintf(
                        "%-50.50s%20.20s%15.15s\n",
                        '{'.str_repeat('*', (strlen($list))-2).'}', $modtime, $size);
               $result .= str_replace('{'.str_repeat('*', (strlen($list))-2).'}', $link, $format);
               // $result .= $link."\n";
               // print ("FORMAT: $format\nRESULT:$result\n");
            }
         }
      } else {
         $result .= $parentlink."\n";
         $result .= "[ EMPTY DIRECTORY ]\n";
      }
      $result .= "</pre>\n";
      return $result;
   }
}