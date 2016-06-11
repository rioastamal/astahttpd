<?php
/**
 * @filesource
 */

/**
 * Class for compressing data using gzip or other format
 * - Created on Fri, 18 Jan 2008 10:40:36 GMT+7
 * - Updated on Thu, 07 Feb 2008 22:35:20 GMT+7
 *
 * @package       astahttpd
 * @subpackage    lib
 * @copyright     Copyright (c) 2008, Rio Astamal
 * @author        Rio Astamal <me@rioastamal.net>
 * @version       0.1
 * @link          http://astahttpd.sourceforge.net/
 * @license       http://opensource.org/licenses/gpl-license.php GNU GPLv3
 * @since         0.1-beta1
 *
 * @property string $inputFile - input file name
 * @property integer $level - compression level
 * @property string $output - compressed data
 * @property string $data - data that needs to be written to file
 * @property string $outputType - could be gzip or deflate(if internal
 * function)
 *
 */

class Compressor {
   /**
    * @var string
    */
   var $inputFile = null;
   /**
    * @var integer
    */
   var $level = null;
   /**
    * @var string
    */
   var $output = null;
   /**
    * @var string
    */
   var $data = null;
   /**
    * @var string
    */
   var $outputType = null;

   /**
    * Constructor
    *
    * @param string $data         data that we want to compress
    * @param integer $level       compression level
    * @param string $input        input file
    */
   public function __construct($data="", $level=5, $input="") {
      $this->level = $level;
      $this->inputFile = $input;
      $this->data = $data;
      $this->outputType = 'gzip';
   }

   /**
    * method to set/change data that will be compressed
    *
    * @param string $sData         data we want to compress
    * @return void
    */
   public function setData($sData) {
      $this->data = $sData;
   }

   /**
    * method to get data (before it being compressed)
    *
    * @return string
    */
   public function getData() {
      return $this->data;
   }

   /**
    * method to set/change compression level
    *
    * @param integer $iLev         compression level
    * @return void
    */
   public function setLevel($iLev) {
      $this->level = $iLev;
   }

   /**
    * method to get compression level
    * @return integer
    */
   public function getLevel() {
      return $this->level;
   }

   /**
    * method to set/change file location
    *
    * @param integer $sFile         file name
    * @return void
    */
   public function setInputFile($sFile) {
      $this->inputFile = $sFile;
   }

   /**
    * method to get file location
    *
    * @return string
    */
   public function getInputFile() {
      return $this->inputFile;
   }

   /**
    * method to set/change output type, i.e. 'gzip'
    *
    * @param string $sType       output type
    * @ return void
    */
   public function setOutputType($sType) {
      $this->outputType = $sType;
   }

   /**
    * method to get output type
    *
    * @return string
   */
   public function getOutputType() {
      return $this->outputType;
   }

   /**
    * method to get/return compressed output
    *
    * @return string
   */
   public function getOutput() {
      $output = "";
      $target = "";
      if ($this->inputFile) {
         $target = $this->inputFile;
      } else {
         $target = tempnam("/tmp", "awsgz");
      }

      if (function_exists('gzopen')) {
         // do gzip compression
         if ($this->outputType == 'gzip') {
            $gz = gzopen($target, 'w'.$this->level);
            gzwrite($gz, $this->data);
            gzclose($gz);
            @unlink($target);
            return file_get_contents($target);
         } else { // we assume 'deflate'
            $output = gzdeflate($this->data, $this->level);
            @unlink($target);
            return $output;   // did not need to unlink
         }
       } else {
         // force type to gzip
         $this->outputType = 'gzip';
         // input file is speciafied by caller
         HttpServer::liveDebug("MODE: EXTERNAL GZIP, WRITE FILE TO: $target\n");
         // write the data to $target
         file_put_contents($target, $this->data);

         // if there is error we redirect to stdout &1
         $proc = popen("gzip -c -{$this->level} $target 2>&1", 'r');
         $output = stream_get_contents($proc);
         // print ("GZIP OUTPUT WAS: $output\n");
         $retval = pclose($proc);

         // if not zero means there's an error
         if ($retval != 0) {
            // reply null string to indicate false value
            $output = '';
         }
         // don't forget to delete the file
      }
      unlink($target);
      return $output;
   }
}