<?php

/**
 * This file demonstrate how astahttpd handle URL Rewriting
 * @package    astahttpd
 * @subpakage  htdocs
 */
 
$act = $_GET['act'];
$cat = $_GET['cat'];
$words = $_GET['words'];
?>
<html>
<head>
<title>astahttpd URL Rewrite Example</title>
<style>
   body {background:#fff; color:#444}
   #main {
      background: #fafafa;
      border:1px solid #ccc;
      padding: 6px;
      margin: 4px auto;
      width:770px;
   }
</style>
</head>
<body>
<div id="main">
<?php
switch ($act) {
      case 'search':
         print("<h2>Action: search</h2>\n");
         print("<h2>Books Category: $cat</h2>\n");
         $words = trim(str_replace('-', ', ', $words));
         print("<h2>Keywords: $words</h2>\n");
         print("<h3><a href=\"/example\">Back</a></h3>\n");
         print("<h2>Source: </h2>");
         print("<div style=\"width:755px;height:300px;overflow:auto\">\n");
         print("<code>");
         highlight_file('rewrite.php');
         print("</code>\n</div>\n");
      break;
      
      default: ?>
         <h1>Books search</h1>
         <script language="javascript">
            function gogogo() {
               var cats = document.frmSearch.cat;
               var cat = '';
               for (var i=0; i<cats.length; i++) {
                  if (cats[i].checked) {
                     cat = cats[i].value;
                     break;
                  }
               }
               var keyword = document.frmSearch.words.value;
               keyword = keyword.replace(/ /g, '-');
               var loc = '/example/search/'+cat+'/'+keyword;
               location.href=loc;
               return false;
            }
         </script>         
         <form action="" method="GET" name="frmSearch">
            <label><strong>Category</strong></label><br>
            <input type="radio" name="cat" id="auto" value="automotive" 
            checked="checked"> Automotive &nbsp;
            <input type="radio" name="cat" id="comp" value="computer">
            Computer &nbsp;
            <input type="radio" name="cat" id="bio" value="biology">
            Biology <br><br>
            <label><strong>Keywords</strong></label><br>
            <input type="text" name="words" id="word" size="30"><br><br>
            <input type="submit" onclick="return gogogo();" value="Search!">
         </form>
         <?php
      break;
}
?>
</div>
</body>
</html>
