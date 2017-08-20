<?php
/*
 * Site-wide header include.  Per-page customizable variables include:
 * string $headerArgs: things to add in the page header
 * array $bodyArgs: body definitions
 *
 * $bodyArgs includes:
 * $bodyArgs['bgcolor']
 * $bodyArgs['textcolor']
 * $bodyArgs['linkcolor']
 * $bodyArgs['vlink']
 * $bodyArgs['alink']
 * $bodyArgs['event']
 *
 * $bodyArgs['event'] allows a page to pass Javascript events to the page.
 * These events are passed as a key/value pair, e.g.:
 *
 * $bodyArgs['event']['onLoad'] = 'window.focus();';
 */

error_reporting(0);
ob_start();

$location = explode('/',$PHP_SELF);
$max = sizeof($location);
if ($title){
	$loc = $title;
}
elseif ($e)  {

	$loc = $e;
}
else  {
	$loc = $location[1];
}

if (empty($bodyArgs['bgcolor'])){

	$bodyArgs['bgcolor'] = "#FFFFFF";
}

if (empty($bodyArgs['textcolor'])){
	$bodyArgs['textcolor'] = "#000000";
}

if (empty($bodyArgs['linkcolor'])){
	$bodyArgs['linkcolor'] = "#B00000";
}

if (empty($bodyArgs['vlink'])){
	$bodyArgs['vlink'] = "#705858";
}

if (empty($bodyArgs['alink'])){
	$bodyArgs['alink'] = "#FF0000";
}



?>
 <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"
xml:lang="en" lang="en">
<head>
<title>somniloquy.org: <?php echo $loc; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>	
<script language="JavaScript" type="text/javascript">
<!--

if (parent.frames.length > 0) {
    parent.location.href = self.document.location
}

//-->
</script>

<style type="text/css">
<!--
a {text-decoration: none;}
a:link {text-decoration: none;}
a:hover {text-decoration: none; color: red;}
a:visited {text-decoration: none;}
.defn {color: blue; font-weight: bold; }
body {  margin: 0; background-color: #ffffff; color: #000000; font-family: Arial, Helvetica, Geneva, sans-serif; font-size: 12px}
td {  font-family: Arial, Helvetica, sans-serif; font-size: 12px; margin: 0;}
table { margin: 0; }
ul {  list-style-type: square}
input { background-color: #FFFFFF; color: #454D63; border: 1px solid #454D63; font-family: verdana,arial,sans-serif; font-size:11px; font-weight:normal; }
.keywords { font-family: Arial, Helvetica, sans-serif; font-size: 10px }
.title {font-family: Arial, Helvetica, Geneva, sans-serif; font-size: 14px}
.comments {font-family: Arial, Helvetica, Geneva, sans-serif; font-size: 11px}
.linebg { background-image: url(http://somniloquy.org/images/linebg.gif) }
.footerline { background-image: url(http://somniloquy.org/images/footer-line.gif) }
.date { font-size: 7pt;}
.cTable { background: #ffffff; }
.cTitle { background: #cccccc; font-family: Verdana, Helvetica, sans-serif; font-size: 10pt; font-weight: bold; }
.cComment { background: #ffffff; font-family: Arial, Geneva, sans-serif; font-size: 10pt; }
.cAuthor { background: #eeeeee; font-family: Arial, Geneva, sans-serif; font-size: 8pt; }
//-->
</style>
<?php if ($headerArgs) echo $headerArgs; ?>
</head>
<body bgcolor="<?php echo $bodyArgs['bgcolor']; ?>" text="<?php echo $bodyArgs['textcolor']; ?>" link="<?php echo $bodyArgs['linkcolor']; ?>" vlink="<?php echo $bodyArgs['vlink']; ?>" alink="<?php echo $bodyArgs['alink']; ?>"<?php

	if (isset($bodyArgs['event'])){

		$events = $bodyArgs['event'];

		while(list($key,$val) = each ($events)){

			echo ' ' . $key . '="' . $val . '"';
		}
	}
	?>>
