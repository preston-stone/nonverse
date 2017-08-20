<?php
include('vendor/autoload.php');
$debugging = '';

$db = new PDO('sqlite:lexicon.default.db');

$config = array (
	'match_word_endings' => true,
	'permit_proper_nouns' => true,
	'use_exceptions_list' => true,
	'use_spellcheck' => true,
	'pspell_dictionary' => "en-us",
	'use_gerund_replacement' => true
);

if ( !isset($_REQUEST['tmpl']) || empty($_REQUEST['tmpl']) ){
    $tmpl = 'wcw';
} else {
	$tmpl = $_REQUEST['tmpl'];
}

function gerund($w){
	global $debugging;
	
	$word = stripslashes($w);
	$lastchar = strlen($word) - 1;
	if ($word{$lastchar} == 'e' && substr($word,-1,2) != 'ee' && $word != 'be'){
		$word = substr($word,0,$lastchar);
	} elseif ($word{$lastchar} == 't' && preg_match("/[aeiou]/i",$word{$lastchar-1})) {
		$word .= 't';
	}
	$word .= 'ing';
	$debugging .= "<li>Gerund: replacing $w with $word</li>";
	return $word;
}

function openTemplate($tmpl){
	$fp = fopen("tmpl/$tmpl.tmpl",'r');
	$file = fread($fp,filesize("tmpl/$tmpl.tmpl"));
	fclose($fp);
	return $file;
}

function insertWords($text,$tag,$array){
	$parts = explode('[' . $tag .']',$text);
	for ($i = 0; $i < sizeof($parts); $i++){
		$j = $i - 1;
		if ($j >= 0){
			$lastchar = strlen($array[$j]) - 1;
			if ($tag == 'verb' && substr($parts[$i],0,2) == 'ed'){
				if (substr($array[$j],-1,1) == 'e'){
					$array[$j] = substr($array[$j],0,$lastchar);
				}elseif (preg_match("/eak$/i",$array[$j])){
					$array[$j] = preg_replace("/eak$/",'o',$array[$j]) . 'ke';
					$parts[$i] = substr($parts[$i],2,strlen($parts[$i]));
				}elseif ($array[$j] == 'go' || $array[$j] == 'do'){
					$array[$j] .= 'ne';
					$parts[$i] = substr($parts[$i],2,strlen($parts[$i]));
				}elseif ($array[$j] == 'be'){
					$array[$j] = 'was';
					$parts[$i] = substr($parts[$i],2,strlen($parts[$i]));
				}elseif ($array[$j] == 'have'){
					$array[$j] = 'had';
					$parts[$i] = substr($parts[$i],2,strlen($parts[$i]));
				}
			}
			if (($tag == 'verb' || $tag =='noun') && substr($parts[$i],0,1) == 's'){
				if (substr($array[$j],-1,1) == 's' || substr($array[$j],-1,1) == 'x' || substr($array[$j],-1,2) == 'ch'){
					$array[$j] = $array[$j] . 'e';
				}elseif ($array[$j] == 'be'){
					$array[$j] = 'i';
					
				}elseif ($array[$j] == 'have'){
					$array[$j] = 'ha';
					
				}elseif (substr($array[$j],-1,1) == 'y' && strlen($array[$j]) > 3){
					//echo $array[$j] . '<br>';
					$array[$j] = substr($array[$j],0,$lastchar) . 'ie';
					//echo $array[$j];
				}
			}

			$parts[$i] = $array[$j] . $parts[$i];
		}
	}	
	$text = implode('',$parts);
	return $text;
}

Function SpellCheck($string) {
	global $debugging;
    $pspell_link = pspell_new("en-us", "", "", "",(PSPELL_FAST|PSPELL_RUN_TOGETHER));
    preg_match_all("/[&;A-Za-z]{1,16}/i", $string, $words);

    for ($i = 0; $i < count($words[0]); $i++) {
		
		if ( !preg_match("/&([a-zA-Z0-9]+);/",$words[0][$i]) ){

        	if (!pspell_check($pspell_link, $words[0][$i])) {
				$debugging .= '<li><b style="font-size: 15px">' . $words[0][$i] . "</b> is misspelled or an unknown word. Options: ";
				$suggestions = pspell_suggest($pspell_link,$words[0][$i]);
				shuffle($suggestions);
				$slist = implode(", ",$suggestions);
				$debugging .= "$slist";
				$debugging .= '<br />';
				$repl = getBestSuggestion($words[0][$i],$suggestions);		
	            $string = preg_replace("/\b" . $words[0][$i] . "\b/i", '<span class="spellcheck" data-orig="' . $words[0][$i] . '">' . $repl . '</span>', $string);    
	        } 
		}
    }
    return $string;
}

function getBestSuggestion($misspelling, array $suggestions)
{
	global $debugging, $config;
	$best_suggestion = null;

	if (count($suggestions) > 0) {

		// check to see if the user entered a lower-case word
		if (ctype_lower($misspelling[0])) {
			
			// if a lower-case word was entered, exclude proper nouns from
			// suggestions
			$best_suggestion = getLowerCaseSuggestion($misspelling,$suggestions);
			// if there was no lower-case suggestion then use the first
			// suggestion
			if ($best_suggestion === null) {
			//	$best_suggestion = $suggestions[0];
			$best_suggestion = $misspelling;

			}
		} else {
			// otherwise, include proper nouns
			$best_suggestion = $misspelling;
		}
	}
//	$debugging .= "<li>Best suggestion is $best_suggestion</li>";
	return $best_suggestion;
}

function checkEnding($word,$suggestion){
	global $debugging;
	//$debugging .= "<li><b>Checking word ending for $word : $suggestion</b></li>";
	$ending_type = false;
	switch( $word ){
		case ( substr($word,-2) == "ly" ) :
			$ending_type = "ly";
			break;
		case ( substr($word,-2) == "'s" ) :
			$ending_type = "'s";
			break;
		case ( substr($word,-3) == "ish" ) :
			$ending_type = "ish";
			break;
		case ( substr($word,-4) == "ness" ) :
			$ending_type = "ness";
			break;
		case ( substr($word,-3) == "ing" ) :
			$ending_type = "ing";
			break;
		case ( substr($word,-3) == "ful" ) :
			$ending_type = "ful";
			break;
		default:
			$ending_type = false;
	}

	if ( $ending_type != false ){
	//	$debugging .= "<li><b>Word ending:</b> Found $ending_type for $word</li>";
		$offset = '-' . strlen($ending_type);
		
		if ( substr($suggestion, -2) == "'s" && substr($word,-2) != "'s" ){
			return false;
		}
		
		if ( substr($suggestion,intval($offset)) == $ending_type  ){
			$debugging .= "<li>'$ending_type' ending match for $suggestion</li>";
			return true;
		}
		else { 
			return false;
		}
	} else {
		return true;
	}	
}

function getLowerCaseSuggestion($word, array $suggestions)
{
	global $debugging,$config;
	
	$match = null;
	$shortest = 5;
	$debugging .= "<ul>";
	$debugging .= "<li><b>Note:</b> Levenshtein threshold is $shortest.</li>";
	foreach ($suggestions as $suggestion) {		
		$word_ending = ($config['match_word_endings'] == true) ? checkEnding($word,$suggestion) : true;
		
		if (ctype_lower($suggestion[0]) == true && !preg_match("/[\s]/i",$suggestion) && !preg_match("/[-,\.:\&;]/i",$suggestion) && preg_match("/[aeiou]/i",$suggestion ) && $word_ending == true ) {
			$lev = levenshtein($word, $suggestion);
			$debugging .= "<li>$suggestion Levenshtein distance: $lev</li>";
			
			if ($lev < $shortest  ) {
		        $match = $suggestion;
		        $shortest = $lev;	
		    }
		}			
	}

	$debugging .= "</ul>";
	$debugging .= "<li><b style='font-size:15px'>Replacing $word with $match : Levenshtein distance $shortest</b><br />&nbsp;<br /></li>";
	return $match;
}

//echo "opening template $tmpl ...<br>";
$file = openTemplate($tmpl);
//echo "file is:<p>$file</p>";
//echo "counting parts of speech ...<br>";
$numVerbs = substr_count($file,'[verb]');
//echo "$numVerbs verbs<br>";
$numNouns = substr_count($file,'[noun]');
//echo "$numNouns nouns<br>";
$numAdjs = substr_count($file,'[adj]');
//echo "$numAdjs adjectives<br>";
$numPreps = substr_count($file,'[prep]');
$numGerunds = substr_count($file,'[gerund]');
$numVerbs = $numVerbs;
$articles = array('the','a');

$word_count = array (
	//'num_conjunctions' => substr_count($file, '[CC]'),
	'num_preps' => substr_count($file, '[IN]'),
	'num_adjs' => substr_count($file, '[JJ]'),
	//'num_adjs_comparative' => substr_count($file, '[JJR]'),
	//'num_adjs_superlative' => substr_count($file, '[JJS]'),
	//'num_modals' => substr_count($file, '[MD]'),
	'num_nouns' => substr_count($file, '[NN]'),
	'num_nouns_pl' => substr_count($file, '[NNS]'),
	//'num_proper_nouns' => substr_count($file, '[NNP]'),
	//'num_proper_nouns_pl' => substr_count($file, '[NNPS]'),
	//'num_predeterminer' => substr_count($file, '[PDT]'),
	//'num_personal_pronouns' => substr_count($file, '[PRP]'),
	//'num_possessive_pronouns' => substr_count($file, '[PRP$]'),
	'num_advs' => substr_count($file, '[RB]'),
	//'num_advs_comparative' => substr_count($file, '[RBR]'),
	//'num_advs_superlative' => substr_count($file, '[RBS]'),
	//'num_particles' => substr_count($file, '[RP]'),
	//'num_interjections' => substr_count($file, '[UH]'),
	'num_verbs' => substr_count($file, '[VB]'),
	'num_verbs_past' => substr_count($file, '[VBD]'),
	'num_verbs_gerund' => substr_count($file, '[VBG]'),
	'num_verbs_past_participle' => substr_count($file, '[VBN]'),
	//'num_verbs_non_3rd_person_present' => substr_count($file, '[VBP]'),
	//'num_verbs_3rd_person_present' => substr_count($file, '[VBZ]')
);

$verbs = array();
$nouns = array();
$adjs = array();
$preps = array();
$gerunds = array();

$replacements = array (
	'CC' => array(),
	'IN' => array(),
	'JJ' => array(),
	'JJR' => array(),
	'JJS' => array(),
	'MD' => array(),
	'NN' => array(),
	'NNS' => array(),
	'NNP' => array(),
	'NNPS' => array(),
	'PDT' => array(),
	'PRP' => array(),
	'PRP$' => array(),
	'RB' => array(),
	'RBR' => array(),
	'RBS' => array(),
	'RP' => array(),
	'UH' => array(),
	'VB' => array(),
	'VBD' => array(),
	'VBG' => array(),
	'VBN' => array(),
	'VBP' => array(),
	'VBZ' => array()
);

function getWords(){


}


$verbquery = "SELECT word FROM lexicon WHERE type = 'verb' ORDER BY random() LIMIT $numVerbs";
$gerquery = "SELECT word FROM lexicon WHERE type = 'verb' ORDER BY random() LIMIT $numGerunds";
$nounquery = "SELECT word FROM lexicon WHERE type = 'noun' ORDER BY random() LIMIT $numNouns";
$adjquery = "SELECT word FROM lexicon WHERE type = 'adj' ORDER BY random() LIMIT $numAdjs";
$prepquery = "SELECT word FROM lexicon WHERE type = 'prep' ORDER BY random() LIMIT $numPreps";

$vq = $db->query($verbquery) or die($verbquery . ' ' . $db->error);

foreach ($vq as $vr){
	$verbs[] = $vr['word'];
}

$nq = $db->query($nounquery);
foreach ($nq as $nr){
	$nouns[] = $nr['word'];
}

$aq = $db->query($adjquery);
foreach ($aq as $ar){
	$adjs[] = $ar['word'];
}

$pq = $db->query($prepquery);
foreach ($pq as $pr){
	$preps[] = $pr['word'];
}

$gq = $db->query($gerquery);
$debugging .= "<h3>Gerund Replacement Routines</h3>";
foreach ($gq as $gr){
	$gerunds[] = gerund($gr['word']);
}

if ( isset($tquery) ){
	
	$tq = $db->query($tquery) or die($db->error);
	
	while ( $tr = $db->fetch_assoc() ){
		$debugging .= '<li> Using text "' . stripslashes($tr['text_title']) . '" by ' . stripslashes($tr['text_author']) . '</li>';
	}
}

$text = insertWords($file,'noun',$nouns);
$text = insertWords($text,'verb',$verbs);
$text = insertWords($text,'adj',$adjs);
$text = insertWords($text,'prep',$preps);
$text = insertWords($text,'gerund',$gerunds);
$poem = explode('[+]',$text);
$pTitle = '<h3>' . $poem[0] . '</h3>';
$text = $poem[1];
$desc = trim($poem[2]);
$url = trim($poem[3]);
$title = "This is just to say";

$exceptions = array ("/seing/i",
					 "/has gived/i",
					 "/gived/i",
					 "/has flyed/i",
					 "/flyed/i",
					 "/tryed/i",
					 "/chs/i",
					 "/understanded/i",
					 "/ dyed/i",
					 "/dyed/i",
					 "/trys /i",
					 "/physicses/i",
					 "/wining /i",
					 "/ited /i",
					 "/stoped/i",
					 "/stoping/i",
					 "/bringed/i",
					 "/sended/i",
					 "/ a a/i",
					 "/ a e/i",
					 "/ a i/i",
					 "/ a o/i",
					 "/ a u/i",
					 "/deads/i",
					 "/childrens/i",
					 "/rys /i",
					 "/shalls/i",
					 "/thinked/i",
					 "/payed/i",
					 "/have falled/i",
					 "/falled/i",
					 "/standed/i",
					 "/plaies /i",
					 "/rryed /i",
					 "/rrys /i",
					 "/teached/i",
					 "/waitting/i",
					 "/losed/i",
					 "/finded/i",
					 "/readed/i",
					 "/runing/i",
					 "/runed/i",
					 "/cuted/i",
					 "/lll/i",
					 "/eatting/i",
					 "/puted/i",
					 "/catched/i",
					 "/have drinked/i",
					 "/drinked/i",
					 "/buyed/i",
					 "/have gave/i",
					 "/have knowed/i",
					 "/knowed/i",
					 "/have seed/i",
					 "/have flew/i",
					 "/have forgeted/i",
					 "/forgeted/i",
					 "/selled/i",
					 "/have spoke/i",
					 "/have taked/i",
					 "/taked/i",
					 "/yed/i",
					 "/maked/i",
					 "/saied/i",
					 "/have drived/i",
					 "/have writed/i",
					 "/writed/i",
					 "/aied/i",
					 "/aies/i",
					 "/have eated/i",
					 "/eated/i",
					 "/mayed/i",
					 "/heared/i",
					 "/have broke/i",
					 "/feeled/i",
					 "/fited/i",
					 "/have ran/i",
					 "/have geted/i",
					 "/geted/i",
					 "/sleeped/i",
					 "/I have bed/i",
					 "/have drawed/i",
					 "/drawed/i",
					 "/have shalled/i",
					 "/spended/i",
					 "/crys/i",
					 "/cryed/i",
					 "/have sited/i",
					 "/wined/i",
					 "/telled/i",
					 "/shs/i",
					 "/shd/i",
					"/&NBS;/",
					"/sheeps/i",
					"/chinese/i"
					 );
					 
$e_repl = array ("seeing",
				 "has given",
				 "gave",
				 "has flown",
				 "flew",
				 "tried",
				 "ches",
				 "understood",
				 " dyed",
				 "died",
				 "tries ",
				 "physics",
				 "winning ",
				 "itted ",
				 "stopped",
				 "stopping",
				 "brought",
				 "sent",
				 " an a",
				 " an e",
				 " an i",
				 " an o",
				 " an u",
				 "dead",
				 "children",
				 "ries ",
				 "shall",
				 "thought",
				 "paid",
				 "have fallen",
				 "fell",
				 "stood",
				 "plays ",
				 "rried ",
				 "rries ",
				 "taught",
				 "waiting",
				 "lost",
				 "found",
				 "read",
				 "running",
				 "ran",
				 "cut",
				 "ll",
				 "eating",
				 "put",
				 "caught",
				 "have drunk",
				 "drank",
				 "bought",
				 "have given",
				 "have known",
				 "knew",
				 "have seen",
				 "have flown",
				 "have forgotten",
				 "forgot",
				 "sold",
				 "have spoken",
				 "have taken",
				 "took",
				 "ied",
				 "made",
				 "said",
				 "have driven",
				 "have written",
				 "wrote",
				 "ayed",
				 "ays",
				 "have eaten",
				 "eaten",
				 "made",
				 "heard",
				 "have broken",
				 "felt",
				 "fitted",
				 "have run",
				 "have gotten",
				 "got",
				 "slept",
				 "I have been",
				 "have drawn",
				 "drew",
				 "have been",
				 "spent",
				 "cries",
				 "cried",
				 "have sighted",
				 "won",
				 "told",
				 "shes",
				 "shed",
				"&nbsp;",
				"sheep",
				"Chinese"
				 );

if ( $config['use_exceptions_list'] == true ){
	$text = preg_replace($exceptions,$e_repl,$text);
}

if ( $config['use_spellcheck'] == true ){	
	$debugging .= "<h3>Spell-Checking</h3>";
	$text = nl2br(SpellCheck($text));
}		 
					 
$bodyArgs['vlink'] = 'maroon';
$meta_desc = "Mad libs-style nonsense verse generator based on the William Carlos Williams poem 'This is just to say.'";
$metaTags = '<meta property="og:title" content="Nonverse Generator" /><meta property="og:description" content="' . $meta_desc . '" />';
include_once("inc/header.inc");
?>
<script>
$(document).ready(function(){
	$("#highlight").click(function(){
		$("span.spellcheck").css("color","#ff0000").css("font-weight","bold").css("cursor","pointer");
		
		$("span.spellcheck").each(function(){
			$(this).attr("title",$(this).attr("data-orig"));
			$(this).addClass('highlighted');
			
		});
		
		
	});
	
	$("span.spellcheck").click(function(){
		var orig = $(this).html();
	//	alert(orig);
		$(this).html($(this).attr('title'));
		$(this).attr('title',orig);
	});
	
	$("#db").click(function(){
		$("div#debugging").toggle();
		
	});
	
});

</script>
<div id="debugging" style="display:none">
<blockquote style="background: #f9f6ed;border: 1px solid #d6d6d8;padding:12px;">
	<h2>Debugging</h2>
	<h3>Parameters</h3>
	<ul>
		<?php
		while ( list($key,$val) = each($config) ){	
			echo "<li><b>$key:</b> $val</li>";
		}
		?>
		
	</ul>
	<?php echo $debugging; ?>
</blockquote>
</div>
<blockquote>
<?php
echo ucwords($pTitle);
echo $text;
?>
<p>
<hr>
<p>This is a nonsense variation of <a href="<?=$url;?>"><?=$desc;?>.</a> The nouns, verbs, adjectives and gerunds have been randomly replaced. <a href="javascript:window.location.reload()">Reload</a> to see a brand new, totally random version.</p>
<p>To see what the template for this poem looks like, <a href="tmpl/<?=$tmpl?>.tmpl">click here</a>.
<p>Check out other auto-butchered poems:</p>
<ul type="circle">
<li> <a href="<?=$PHP_SELF;?>?tmpl=wcw">"This is just to say,"</a> William Carlos Williams
<li> <a href="<?=$PHP_SELF;?>?tmpl=yeats">"Leda and the Swan,"</a> William Butler Yeats
<li> <a href="<?=$PHP_SELF;?>?tmpl=shakespeare">"The Seven Ages Of Man,"</a> William Shakespeare
</ul>
<li><a id="highlight" href="javascript:;">Highlight spell-corrected words</a> (hint: click on highlighted words to see the original un-spell-corrected words)</li>
<li><a id="db" href="javascript:;">Debug</a></li>
</body>
</html>

