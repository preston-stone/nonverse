<?php
require('lib/nonverse.class.php');

if ( !isset($_REQUEST['tmpl']) || empty($_REQUEST['tmpl']) ){
    $tmpl = 'wcw';
} else {
	$tmpl = $_REQUEST['tmpl'];
}
$poem = new Nonverse($tmpl);
//$config = array('use_spellcheck' => false);
//$poem->setConfig($config);
$poem->process();
$pTitle = $poem->text[0];
$text = $poem->text[1];
$desc = trim($poem->text[2]);
$url = trim($poem->text[3]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Nonverse: <?=$pTitle?></title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <link rel="stylesheet" href="static/app.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script src="static/app.js"></script>
</head>
<body>
<nav class="navbar navbar-inverse">
  <div class="container-fluid">
    <div class="navbar-header">
      <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar">
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>                        
      </button>
      <a class="navbar-brand" href="#">Nonverse</a>
    </div>
    <div class="collapse navbar-collapse" id="myNavbar">
      <ul class="nav navbar-nav">
        <li><a id="reload" href="#">Reload</a></li>
        <li><a id="db" href="#">Debug</a></li>
        <li class="dropdown"><a class="dropdown-toggle" data-toggle="dropdown" href="#">Templates <span class="caret"></span></a>
        	<ul class="dropdown-menu">
			    <li><a href="?tmpl=wcw">this is just to say</a></li>
			    <li><a href="?tmpl=yeats">Leda and the Swan</a></li>
			    <li><a href="?tmpl=shakespeare">The Seven Ages of Man</a></li>
			    <li><a href="?tmpl=cummings">Summer Silence</a></li>
			 </ul>
			</li>
        <li><a href="#">Contact</a></li>
      </ul>
    </div>
  </div>
</nav>
  
<div class="container-fluid text-center">    
  <div class="row content">
    <div class="col-sm-2 sidenav">
      
    </div>
    <div class="col-sm-8 text-left"> 
<div id="debugging" style="display:none">
<blockquote style="background: #f9f6ed;border: 1px solid #d6d6d8;padding:12px;">
	<h2>Debugging</h2>
	<h3>Parameters</h3>
	<ul>
		<?php
		while ( list($key,$val) = each($poem->config) ){	
			echo "<li><b>$key:</b> $val</li>";
		}
		?>
		
	</ul>
	<h3>Spell Check</h3>
	<p>Using <?=$poem->debugging['spellchecker']?></p>
	<?php
	foreach($poem->debugging['words'] as $d){
	?>
	<p><b><?=$d['word']?></b> is misspelled or not a word. Suggestions: <?=$d['options']?></p>
	<ul>
	<?php foreach ($d['suggestions'] as $s){ ?>
	<li> <?=$s['suggestion']?>: Levenshtein distance <?=$s['levenshtein']?></li>
	<?php
	}
	?>
	<p>Replaced <b><?=$d['word']?></b> with <b><?=$d['match']?></b></p>
	</ul>
	<?php
	}
	?>
</blockquote>
</div>
<blockquote>
<h3 class="poemTitle">
<?php
echo ucwords($pTitle);
?></h3>
<?php
echo $text;
?>

<p>
<hr>
<p>This is a nonsense variation of <a href="<?=$url;?>"><?=$desc;?>.</a> The nouns, verbs, adjectives and gerunds have been randomly replaced. <a href="javascript:window.location.reload()">Reload</a> to see a brand new, totally random version.</p>
<p>To see what the template for this poem looks like, <a href="tmpl/<?=$tmpl?>.tmpl">click here</a>.
<p>Check out other auto-butchered poems:</p>
<ul type="circle">
<li> <a href="?tmpl=wcw">"This is just to say,"</a> William Carlos Williams
<li> <a href="?tmpl=yeats">"Leda and the Swan,"</a> William Butler Yeats
<li> <a href="?tmpl=shakespeare">"The Seven Ages Of Man,"</a> William Shakespeare
<li> <a href="?tmpl=cummings">"Summer Silence,"</a> ee cummings
</ul>
<li><a id="highlight" href="javascript:;">Highlight spell-corrected words</a> (hint: click on highlighted words to see the original un-spell-corrected words)</li>
 </div>
    <div class="col-sm-2 sidenav">
    </div>
  </div>
</div>

<footer class="container-fluid text-center">
  <p></p>
</footer>

</body>
</html>
