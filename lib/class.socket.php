<?php
/**
 * @filesource
 */
 
/**
 * This class provide basic TCP Socket object
 * - Created on Fri, 18 Jan 2008 10:40:36 GMT+7
 * - Updated on Sat, 09 Feb 2008 11:11:44 GMT+7 
 *
 * @package       astahttpd
 * @subpackage    lib
 * @copyright     Copyright (c) 2008, Rio Astamal
 * @author        Rio Astamal <c0kr3x@gmail.com>
 * @version       0.2
 * @link          http://astahttpd.sourceforge.net/
 * @license       http://opensource.org/licenses/gpl-license.php GNU GPLv3
 * @since         0.1-alpha
 * 
 * @property string $host - hostname IP address
 * @property integer $listenPort - port number
 * @property resource $socketObject - main socket resource
 * @property resource $spawnObject - spawned socket
 */
class Socket {
   /**
    * @var string
    */
   private $host = null; 
   /**
    * @var integer
    */
   private $listenPort = null; 
   /**
    * @var resource
    */
   private $socketObject = null; 
   /**
    * @var resource
    */
   private $spawnObject = null;
   
   /**
    * Constructor
    *  
    * @param integer $port      port number that socket will listen to (optional)
    * @param string  $host      hostname (optional)
    */
   public function __construct($port = 10000, $host = "127.0.0.1") {
      $this->host = $host;
      $this->listenPort = $port;
      // let's try to create TCP socket
      $this->socketObject = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or
      $this->_throwSocketError("Could not create socket");
      
      if (!socket_set_option($this->socketObject, SOL_SOCKET, SO_REUSEADDR, 1))
         $this->_throwSocketError("Could not set socket option");   
               
      // bind the source address
      if (!socket_bind($this->socketObject, 0, $this->listenPort))
         $this->_throwSocketError("Could not bind the socket. ".
         "Make sure you're running as root if the port number below 1024");
         
      socket_getsockname($this->socketObject, $this->host, $this->listenPort);
         
      // $this->unBlockSocket();
   }
   
   /**
    * method to set hostname
    *
    * @param string $sHost         string hostname
    * @return void
    *
    */
   public function setHost($sHost) {
      $this->host = $sHost;
   }
   
   /**
    * method to get hostname
    *
    * @return string
    */
   public function getHost() {
      return $this->host;
   }
   
   /**
    * method for setting port number that socket will listen to
    * 
    * @param integer $iPort         port number
    * @return void
    */
   public function setListenPort($iPort) {
      $this->listenPort = $iPort;
   }
   
   /**
    * method for getting port number
    *
    * @return integer
    */
   public function getListenPort() {
      return $this->listenPort;
   }
   
   /**
    * method to start listening for incoming data
    *
    * @return void
    */
   public function startListen() {
      // start listening
      if (!socket_listen($this->socketObject, 256))
         $this->_throwSocketError("Could not listen to the port");
         
      return $this->socketObject;
   }
   
   /**
    * method to accept client connection
    *
    * @return void
    */
   public function acceptConnection() {
      // be ready for incoming connection
      $this->spawnObject = socket_accept($this->socketObject) or
         $this->_throwSocketError("Could not set up listener");
         
      return $this->spawnObject;
   }
   
   /**
    * method to read data that client sent
    *
    * @param integer $length           maximum data needs to be read
    * @return string
    */
   public function readData($length=2048) {
      // read incoming data
      $input = socket_read($this->spawnObject, $length, PHP_BINARY_READ);
      if (!$input)
         $this->_throwSocketError("Could not read incoming data");
         
      return $input;
   }
   
   /**
    * method to send data to the client
    *
    * @param string $data         sent data
    * @return void
    */
   public function sendData($data) {
      if (!socket_write($this->spawnObject, $data, strlen($data)))
         $this->_throwSocketError("Could not send data");
   }
   
   /**
    * method to set blocking mode to UNBLOCK mode
    *
    * @return void
    */
   public function unBlockSocket() {
      if (!socket_set_nonblock($this->socketObject))
         $this->_throwSocketError("Could not set socket to unblocking mode");
   }
   
   /**
    * method to set blocking mode to BLOCK mode
    *
    * @return void
    */
   public function blockSocket() {
      if (!socket_set_block($this->socketObject))
         $this->_throwSocketError("Could not set socket to blocking mode");
   }
   
   /**
    * this method provide multiplexing I/O for socket, so we can read/write
    * at the same time without having to turn on non-blocking mode
    */
   public function multiplexIO(&$read_set, &$write_set=NULL, &$exc_set=NULL, $timeout=NULL) {
      
   }
   
   /**
    * method to get client's IP address
    *
    * @return string
   */
   public function getPeerName() {
      socket_getpeername($this->spawnObject, $addr);
      return $addr;
   }
   
   /**
    * method to stop socket for reading, sending, or both
    *
    * @param integer $type            shutdown value, 0=read, 1=write, 2=both
    * @return void
    */
   public function shutdownSocket($type=2) {
      return socket_shutdown($this->socketObject, $type);
   }
   
   /**
    * method to close current spawned socket object that accept connection
    *
    * @return void
    */
   public function closeSpawn() {
      // close the child / spawned socket
      return socket_close($this->spawnObject);
   }
   
   /**
    * method to close socket resource
    *
    * @return void
    */
   public function closeSocket() {
      // just in case close again the spawn
      // socket_close($this->spawnObject);
      return socket_close($this->socketObject);
   }
   
   /**
    * method to throw last socket error that occurs
    *
    * @return void
    */
   private function _throwSocketError($mesg) {
      throw new Exception($mesg." (".socket_last_error().").".
      "\nError Message: ".socket_strerror(socket_last_error())."\n");
   }
}

?>
