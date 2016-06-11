<?php

$manual_dir = $_SERVER['DOCUMENT_ROOT'].'/astahttpd_api';
if (!file_exists($manual_dir)) {
print <<<END
<h3>Could not find 'astahttpd_api' directory on {$_SERVER['DOCUMENT_ROOT']}.</h3>
<p>Make sure the directory is exists.<br> You can manually copy the 'astahttpd_api'
directory to {$_SERVER['DOCUMENT_ROOT']} or, <br> you can use alias_dir to map the
astahttpd_api directory then you can visit it at 
<a href="http://{$_SERVER['HTTP_HOST']}/astahttpd_api/">
http://{$_SERVER['HTTP_HOST']}/astahttpd_api/</a>.<br><br>

You can download astahttpd API/Manual at 
<a href="https://sourceforge.net/project/showfiles.php?group_id=215337">
download page</a>.
END;
} else {
   header("Location: http://{$_SERVER['HTTP_HOST']}/astahttpd_api/");
}
?>
