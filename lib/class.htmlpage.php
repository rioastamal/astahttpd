<?php
/**
 * @filesource
 */

/**
 * Class to build an HTML page
 * - Created on Sun, 20 Jan 2008 18:53:43 GMT+7
 * - Updated on Thu, 07 Feb 2008 22:36:30 GMT+7
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
 * @property string $title - document title
 * @property string $css - CSS Style
 * @property string $body - document body
 * @property string $docType - HTML document type
 * @property string $js - javascript
 *
 */

class HtmlPage {
   /**
    * @var string
    */
   private $title = null;
   /**
    * @var string
    */
   private $css = null;
   /**
    * @var string
    */
   private $body = null;
   /**
    * @var string
    */
   private $docType = null;
   /**
    * @var string
    */
   private $js = null;

   /**
    * Constructor
    *
    * @param string $title          HTML title
    * @param string $body           HTML body
    */
   public function __construct($title='', $body='') {
      $this->body = $body;
      $this->title = $title;
      $this->docType = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML ';
      $this->docType .= '1.0 Transitional//EN" ';
      $this->docType .= '"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'."\n";

      $style = "body{ background:#fafafa; color:#333; }\n";
      $style .= "a{ text-decoration:none; color:#002378; }\n";
      $style .= "a:visited{ color: #002378; }a:hover{ font-weight:bold; }\n";
      $style .= ".bold{ font-weight:bold;}.italic{ font-style:italic;}\n";
      $style .= ".underline{ text-decoration:underline;}\n";

      $this->css = $style;
      $this->js = '';
   }

   /**
    * method to set/change the document title
    *
    * @param string $sTitle         HTML title
    * @return void
    */
   public function setTitle($sTitle) {
      $this->title = $sTitle;
   }

   /**
    * method to get document title
    *
    * @return string
   */
   public function getTitle() {
      return $this->title;
   }

   /**
    * method to set/change document type
    *
    * @param string $sDoc         string doctype
    * @return void
    */
   public function setDocType($sDoc) {
      $this->docType = $sDoc;
   }

   /**
    * method to get document type
    *
    * @return string
    */
   public function getDocType() {
      return $this->docType;
   }

   /**
    * method to set/change CSS style of the document
    *
    * @param string $sCss         CSS
    * @return void
    */
   public function setCss($sCss) {
      $this->css = $sCss;
   }

   /**
    * method to get CSS style of the document
    *
    * @return string
   */
   public function getCss() {
      return $this->css;
   }

   /**
    * method to set/change Javascript of the document
    *
    * @param string $sJs         Javascript
    * @return void
   */
   public function setJs($sJs) {
      $this->js = $sJs;
   }

   /**
    * method to get javascript of the document
    *
    * @return string
   */
   public function getJs() {
      return $this->js;
   }

   /**
    * method to set/change document body
    *
    * @param string $sBody         HTML body
    * @return void
    */
   public function setBody($sBody) {
      $this->body = $sBody;
   }

   /**
    * method to get document body
    *
    * @return string
    */
   public function getBody() {
      return $this->body;
   }

   /**
    * method to add CSS, this method does not override previous CSS
    *
    * @param string $sCss         CSS
    * @return void
    */
   public function addCss($sCss) {
      $this->css .= $sCss."\n";
   }

   /**
    * method to add Javascript, this method does not override previous javascript
    *
    * @param string $sJs         javascript
    * @return void
    */
   public function addJs($sJs) {
      $this->js .= $sJs."\n";
   }

   /**
    * method to add document bodym this method does not override previous body
    *
    * @param string $sBody         HTML body
    * @return void
    */
   public function addBody($sBody) {
      $this->body .= $sBody."\n";
   }

   /**
    * method to buil complete HTML page
    *
    * @return void
   */
   public function buildPage() {
      $result = $this->docType."\n".
                '<html xmlns="http://www.w3.org/1999/xhtml">'."\n".
                '<head>'."\n".
                '<title>'.$this->title.'</title>'."\n".
                '<style type="text/css">'.$this->css.'</style>'."\n".
                '<script language="javascript">'.$this->js.'</script>'."\n".
                '</head>'."\n".
                '<body>'."\n".
                $this->body."\n".
                '</body>'."\n".
                '</html>';
      return $result;
   }
}