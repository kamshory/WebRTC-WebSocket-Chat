<?php
date_default_timezone_set('Asia/Jakarta');
header("Content-Type: text/javascript");
$expires = 3600*24*365;
header("Pragma: public");
header("Cache-Control: maxage=".$expires);
header('Expires: ' . gmdate('D, d M Y H:i:s', time()+$expires) . ' GMT');
include_once dirname(__FILE__)."/lang.php";
$lang_id = 'id';
$lang_str = "var lang_id='".$lang_id."';if(typeof Language=='undefined'){var Language={};}Language[lang_id]=".json_encode($language_res[$language_id]).";";
header("Content-Length: ".strlen($lang_str));
echo $lang_str;
?>
