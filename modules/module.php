<?php
/**
 * @filesource
 */

/**
 * Abstract class for prototyping modules, all modules should extends this class
 * - Created on Thu, 07 Feb 2008 18:53:37 GMT
 *
 * @package       astahttpd
 * @subpackage    modules
 * @copyright     Copyright (c) 2008, Rio Astamal
 * @author        Rio Astamal <c0kr3x@gmail.com>
 * @version       0.1
 * @link          http://astahttpd.sourceforge.net/
 * @license       http://opensource.org/licenses/gpl-license.php GNU GPLv3
 * @since         0.1-beta3
 *
 * @property-read string $modName - module's name
 * @property-read string $modVer - module's version
 * @property-read string $modAuthor - author name
 * @property-read string $modType - Type of module, i.e. standard, security, etc
 * @property-read string $modCycle - Execution cycle
 * @property-read integer $modPriority - Order of execution, relative to cycle
 * @property-read string $modDesc - Short description of the module
 * @property-read string $serverVersion - Server version number
 *
 * @property-read HttpServer $O_HttpServer - reference of $httpServer
 * @property-read HttpHeaderMaker $O_HMaker - reference of $htbuilder
 * @property-read HttpHeaderParser $O_HParser - reference of $htparser
 * 
 * @property-read string $V_Request - reference of $request
 * @property-read string $V_Content - reference of $content
 * @property-read string $V_Request - reference of $request
 * @property-read string $V_CgiHeader - reference of $cgiHeader
 * @property-read boolean $V_StaticContent - reference of $staticContent
 * @property-read array $V_AuthConf - reference of $authConf
 * @property-read array $V_RewEngineConf - reference of $rewEngineConf
 * @property-read array $V_RewriteConf - reference of $rewriteConf
 * @property-read integer $V_BootTime - reference of $bootTime
 * @property-read array $V_AwsModules - reference of $awsModules
 * @property-read array $V_BandwidthConf - reference of $bandwidthConf
 */
abstract class Module {
   /**
    * @var string
    */
   protected $modName = null;
   /**
    * @var string
    */
   protected $modVer = null;
   /**
    * @var string
    */
   protected $modAuthor = null;
   /**
    * @var string
    */
   protected $modType = null;
   /**
    * @var string
    */
   protected $modCycle = null;
   /**
    * @var integer
    */
   protected $modPriority = null;
   /**
    * @var string
    */
   protected $modDesc = null;
   
   /**
    * @var HttpServer
    */
   protected $O_HttpServer = null;
   /**
    * @var HttpHeaderMaker
    */
   protected $O_HMaker = null;
   /**
    * @var HttpHeaderParser
    */
   protected $O_HParser = null;
   /**
    * @var string
    */
   protected $V_Content = null;
   /**
    * @var string
    */
   protected $V_Request = null;
   /**
    * @var string
    */
   protected $V_CgiHeader = null;
   /**
    * @var boolean
    */
   protected $V_StaticContent = null;
   /**
    * @var array
    */
   protected $V_AuthConf = null;
   /**
    * @var array
    */
   protected $V_RewEngineConf = null;
   /**
    * @var array
    */
   protected $V_RewriteConf = null;
   /**
    * @var integer
    */
   protected $V_BootTime = null;
   /**
    * @var array
    */
   protected $V_AwsModules = null;
   /**
    * @var array
    */
   protected $V_BandwidthConf = null;
   
   /**
    * Class constructor
    */
   public function __construct() {
      $this->modName = 'Module Name';
      $this->modVer = '0.1';
      $this->modAuthor = 'Your Name';
      $this->modType = 'standard';     // security, parser, etc,
      $this->modCycle = 'first_cycle';
      $this->modPriority = 0;
      $this->modDesc = 'Description of module';
      
      // list of available global members for modules
      $this->O_HttpServer =& $GLOBALS['httpServer'];
      $this->O_HMaker =& $GLOBALS['htbuilder'];
      $this->O_HParser =& $GLOBALS['htparser'];
      
      $this->V_Request =& $GLOBALS['request'];
      $this->V_Content =& $GLOBALS['content'];
      $this->V_RespHeader =& $GLOBALS['responseHeader'];
      $this->V_CgiHeader =& $GLOBALS['cgiHeader'];
      $this->V_StaticContent =& $GLOBALS['staticContent'];
      $this->V_AuthConf =& $GLOBALS['authConf'];
      $this->V_RewEngineConf =& $GLOBALS['rewEngineConf'];
      $this->V_RewriteConf =& $GLOBALS['rewriteConf'];
      $this->V_BootTime =& $GLOBALS['bootTime'];
      $this->V_AwsModules =& $GLOBALS['awsModules'];
      $this->V_BandwidthConf =& $GLOBALS['bandwidthConf'];
      $this->V_CgiEnv =& $GLOBALS['cgiEnv'];
      $this->V_Target =& $GLOBALS['target'];
   }
   
   /**
    * method to get module name
    *
    * @return string
    */
   public function getModName() {
      return $this->modName;
   }
   
   /**
    * method to get module version
    *
    * @return string
    */
   public function getModVer() {
      return $this->modVer;
   }
   
   /**
    * method to get name of author
    *
    * @return string
    */
   public function getModAuthor() {
      return $this->modAuthor;
   }
   
   /**
    * method to get type of module
    *
    * @return string
    */
   public function getModType() {
      return $this->modType;
   }
   
   /**
    * method to get module cycle
    *
    * @return string
    */
   public function getModCycle() {
      return $this->modCycle;
   }
   
   /**
    * method to modul execution priority
    *
    * @return integer
    */   
   public function getModPriority() {
      return $this->modPriority;
   }
   
   /**
    * method to get module description
    *
    * @return string
    */
   public function getModDesc() {
      return $this->modDesc;
   }
   
   /**
    * Main method of module, module are activated by calling this method.
    * All child MUST override this method.
    *
    * @return void
    */
   public abstract function activate();
   
}

?>
