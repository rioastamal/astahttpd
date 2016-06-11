<?php
/**
 * @filesource
 */
 
/**
 * Class for writing log to a file
 * - Created on Fri, 01 Feb 2008 10:03:48 GMT+7
 *
 * @package       astahttpd
 * @subpackage    lib
 * @copyright     Copyright (c) 2008, Rio Astamal
 * @author        Rio Astamal <c0kr3x@gmail.com>
 * @version       0.1
 * @link          http://astahttpd.sourceforge.net/
 * @license       http://opensource.org/licenses/gpl-license.php GNU GPLv3
 * @since         0.1-beta2
 * 
 * @property string $logData - log data written to file
 * @property string $logFile - File log location
 * @property string $delim - delimiter foreach data in same line
 * @property string $new - new line character, default \n
 * @property integer|float $maxSize - max size of the log file before create 
 * backup file
 * @property integer|float $logSize - current size of the log file
 * @property string $backupDir - backup directory
 *
 */
 
class Logger {
   private $logData = null;
   private $logFile = null;
   private $delim = null;
   private $newLine = null;
   private $maxSize = null;
   private $logSize = null;
   private $backupDir = null;
   
   /**
    * Contructor
    *
    * @param string $logFile            location of the log file
    * @param string $data               data written to log file
    */
   public function __construct($logFile="/tmp/access_log", $data="") {
      $this->logFile = $logFile;
      $this->delim = " - ";
      $this->newLine = "\n";
      $this->maxSize = 10000; // default 10 mb
      $this->logData = $data;
      $this->logSize = filesize($logFile) / 1024;  // in kb
      $this->backupDir = dirname($logFile);
   }
   
   /**
    * method to set/change log file location
    *
    * @param string $sPath            log file
    * @return void
    */
   public function setlogFile($sPath) {
      $this->logFile = $sPath;
   }
   
   /**
    * method to get log file location
    *
    * @return string
    */
   public function getLogFile() {
      return $this->logFile;
   }
   
   /**
    * method to add log data (does not overwrite log data on current object
    *
    * @param string $sData            log data
    * @return void
    */
   public function addLogData($sData) {
      $this->logData .= $this->delim.$sData;
   }
   
   /**
    * method set/change log data
    *
    * @param string $sData            data that will be write to
    * @return void
    */
   public function setLogData($sData) {
      $this->logData = $sData;
   }
   
   /**
    * method to get log data on current object
    *
    * @return string
    */
   public function getLogData() {
      return $this->logData;
   }
   
   /**
    * method to set/change delimiter
    *
    * @param string $sDel            delimiter, i.e ' - '
    * @return void
    */
   public function setDelim($sDel) {
      $this->delim = $sDel;
   }
   
   /**
    * method to get current delimiter used on log file
    *
    * @return string
    */
   public function getDelim() {
      return $this->delim;
   }
   
   /**
    * method to set/change new line character, i.e "\n"
    * NOTE: USE DOUBLE QUOTE NOT SINGLE QUOTE
    *
    * @param string $sLine            new line character
    * @return void
    */
   public function setNewLine($sLine) {
      $this->newLine = $sLine;
   }
   
   /**
    * method get new line character
    *
    * @return string
    */
   public function getNewLine() {
      return $this->newLine;
   }
   
   /**
    * method to set/change maximum size of log file
    *
    * @param integer|float $iSize            maximum size in kb
    * @return void
    */
   public function setMaxSize($iSize) {
      $this->maxSize = $iSize;
   }
   
   /**
    * method to get maximum size of log file
    *
    * @return integer|float
    */
   public function getMaxSize() {
      return $this->maxSize;
   }
   
   /**
    * method to get fize of log file
    *
    * @return integer|float
    */
   public function getLogSize() {
      return filesize($this->logFile) / 1024;
   }
   
   /**
    * method to set/change path of backup directory
    *
    * @param string $sPath            path to directory
    */
   public function setBackupDir($sPath) {
      $this->backupDir = $sPath;
   }
   
   /**
    * method to get backup directory path
    *
    * @return string
    */
   public function getBackupDir() {
      return $this->backupDir;
   }
   
   /**
    * method to write the log data to file
    *
    * @return void
    */
   public function writeLog() {
      touch($this->logFile);
      if (is_writeable($this->logFile)) {
         HttpServer::liveDebug("SIZE COMPARE: {$this->logSize} vs {$this->maxSize}");
         if ($this->logSize > $this->maxSize) {
            $newName = $this->backupDir.'/'.date('dmYHis').'.bak';
            if (!copy($this->logFile, $newName)) {
               trigger_error("Could not write backup to $newName, make sure
               the directory is writeable by astahttpd.", E_USER_WARNING);
            } else {
               // empty the file
               file_put_contents($this->logFile, '');
            }
         }
         
         file_put_contents($this->logFile, $this->logData.$this->newLine,
                           FILE_APPEND);
      } else {
         trigger_error("Could not write log to {$this->logFile}, make sure it's
         writeable by astahttpd.", E_USER_WARNING);
      }
   }
}
?>
