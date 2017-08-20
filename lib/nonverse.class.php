<?php
/**
 * Nonsensical verse generator. This app parses mad libs-style templates and replaces parts of speech with relevant
 * words from a database. Currently, this app requires Enchant for advanced functionality.
 *
 * To-do: replace Enchant functionality with a composer spellcheck package (or API to a dictionary service)
 */
class Nonverse {
	public $tmpl;
	public $dbname = 'lexicon.default.db';
	public $db;
	public $debugging;
	public $text;
	public $config = array (
		'match_word_endings' => true,
		'permit_proper_nouns' => true,
		'use_exceptions_list' => true,
		'use_spellcheck' => true,
		'spellcheck_dictionary' => "en_US",
		'use_gerund_replacement' => true,
		'spellcheck_levenshtein_distance' => 5
	);
	public $tpldata;
	private $exceptions = array ("/seing/i",
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
	private $e_repl = array ("seeing",
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

	public function __construct($tmpl, $dbname = ''){
		$this->tmpl = $tmpl;

		if ( $dbname != ''){ 
			$this->dbname = 'lexicon.' . $dbname . '.db'; 
		}
		$this->db = new PDO('sqlite:'.$this->dbname);
		$this->process();
	}

	protected function openTemplate(){
		$fp = fopen("tmpl/".$this->tmpl.".tmpl",'r');
		$this->tpldata = fread($fp,filesize("tmpl/$this->tmpl.tmpl"));
		fclose($fp);
	}

	protected function insertWords($text,$tag,$array){
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
											$array[$j] = substr($array[$j],0,$lastchar) . 'ie';
					}
				}
				$parts[$i] = $array[$j] . $parts[$i];
			}
		}	
		$text = implode('',$parts);
		return $text;
	}

	protected function spellCheck($string){
		$enchant = enchant_broker_init();
		$spell = enchant_broker_request_dict($enchant, $this->config['spellcheck_dictionary']);
	    preg_match_all("/[&;A-Za-z]{1,16}/i", $string, $words);

	    for ($i = 0; $i < count($words[0]); $i++) {
			
			if ( !preg_match("/&([a-zA-Z0-9]+);/",$words[0][$i]) ){

	        	if (!enchant_dict_check($spell, $words[0][$i])) {
					$this->debugging .= '<li><b style="font-size: 15px">' . $words[0][$i] . "</b> is misspelled or an unknown word. Options: ";
					$suggestions = enchant_dict_suggest($spell,$words[0][$i]);
					shuffle($suggestions);
					$slist = implode(", ",$suggestions);
					$this->debugging .= "$slist";
					$this->debugging .= '<br />';
					$repl = $this->getBestSuggestion($words[0][$i],$suggestions);		
		            $string = preg_replace("/\b" . $words[0][$i] . "\b/i", '<span class="spellcheck" data-orig="' . $words[0][$i] . '">' . $repl . '</span>', $string);    
		        } 
			}
	    }
	    return $string;	
	}

	protected function getBestSuggestion($misspelling, $suggestions){
		$best_suggestion = null;

		if (count($suggestions) > 0) {

			// check to see if the user entered a lower-case word
			if (ctype_lower($misspelling[0])) {
				
				// if a lower-case word was entered, exclude proper nouns from
				// suggestions
				$best_suggestion = $this->getLowerCaseSuggestion($misspelling,$suggestions);
				// if there was no lower-case suggestion then use the first
				// suggestion
				if ($best_suggestion === null) {
				$best_suggestion = $misspelling;

				}
			} else {
				// otherwise, include proper nouns
				$best_suggestion = $misspelling;
			}
		}
		return $best_suggestion;
	}

	protected function getLowerCaseSuggestion($word, $suggestions){		
		$match = null;
		$shortest = $this->config['spellcheck_levenshtein_distance'];
		$this->debugging .= "<ul>";
		$this->debugging .= "<li><b>Note:</b> Levenshtein threshold is $shortest.</li>";

		foreach ($suggestions as $suggestion) {		
			$word_ending = ($this->config['match_word_endings'] == true) ? $this->checkEnding($word,$suggestion) : true;
			
			if (ctype_lower($suggestion[0]) == true && !preg_match("/[\s]/i",$suggestion) && !preg_match("/[-,\.:\&;]/i",$suggestion) && preg_match("/[aeiou]/i",$suggestion ) && $word_ending == true ) {
				$lev = levenshtein($word, $suggestion);
				$this->debugging .= "<li>$suggestion Levenshtein distance: $lev</li>";
				
				if ($lev < $shortest  ) {
			        $match = $suggestion;
			        $shortest = $lev;	
			    }
			}			
		}
		$this->debugging .= "</ul>";
		$this->debugging .= "<li><b style='font-size:15px'>Replacing $word with $match : Levenshtein distance $shortest</b><br />&nbsp;<br /></li>";
		return $match;	
	}

	private function gerund($w){
		$word = stripslashes($w);
		$lastchar = strlen($word) - 1;
		if ($word{$lastchar} == 'e' && substr($word,-1,2) != 'ee' && $word != 'be'){
			$word = substr($word,0,$lastchar);
		} elseif ($word{$lastchar} == 't' && preg_match("/[aeiou]/i",$word{$lastchar-1})) {
			$word .= 't';
		}
		$word .= 'ing';
		$this->debugging .= "<li>Gerund: replacing $w with $word</li>";
		return $word;
	}

	private function checkEnding($word, $suggestion){
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
			$offset = '-' . strlen($ending_type);
			
			if ( substr($suggestion, -2) == "'s" && substr($word,-2) != "'s" ){
				return false;
			}
			
			if ( substr($suggestion,intval($offset)) == $ending_type  ){
				$this->debugging .= "<li>'$ending_type' ending match for $suggestion</li>";
				return true;
			}
			else { 
				return false;
			}
		} else {
			return true;
		}	
	}

	public function process(){

		$this->openTemplate();
		$numVerbs = substr_count($this->tpldata,'[verb]');
		$numNouns = substr_count($this->tpldata,'[noun]');
		$numAdjs = substr_count($this->tpldata,'[adj]');
		$numPreps = substr_count($this->tpldata,'[prep]');
		$numGerunds = substr_count($this->tpldata,'[gerund]');
		$articles = array('the','a');
		$verbs = array();
		$nouns = array();
		$adjs = array();
		$preps = array();
		$gerunds = array();
		$verbquery = "SELECT word FROM lexicon WHERE type = 'verb' ORDER BY random() LIMIT $numVerbs";
		$gerquery = "SELECT word FROM lexicon WHERE type = 'verb' ORDER BY random() LIMIT $numGerunds";
		$nounquery = "SELECT word FROM lexicon WHERE type = 'noun' ORDER BY random() LIMIT $numNouns";
		$adjquery = "SELECT word FROM lexicon WHERE type = 'adj' ORDER BY random() LIMIT $numAdjs";
		$prepquery = "SELECT word FROM lexicon WHERE type = 'prep' ORDER BY random() LIMIT $numPreps";
		$vq = $this->db->query($verbquery) or die($verbquery . ' ' . $this->db->error);

		foreach ($vq as $vr){
			$verbs[] = $vr['word'];
		}

		$nq = $this->db->query($nounquery);
		foreach ($nq as $nr){
			$nouns[] = $nr['word'];
		}

		$aq = $this->db->query($adjquery);
		foreach ($aq as $ar){
			$adjs[] = $ar['word'];
		}

		$pq = $this->db->query($prepquery);
		foreach ($pq as $pr){
			$preps[] = $pr['word'];
		}

		$gq = $this->db->query($gerquery);
		$this->debugging .= "<h3>Gerund Replacement Routines</h3>";
		foreach ($gq as $gr){
			$gerunds[] = $this->gerund($gr['word']);
		}
		$this->text = $this->insertWords($this->tpldata,'noun',$nouns);
		$this->text = $this->insertWords($this->text,'verb',$verbs);
		$this->text = $this->insertWords($this->text,'adj',$adjs);
		$this->text = $this->insertWords($this->text,'prep',$preps);
		$this->text = $this->insertWords($this->text,'gerund',$gerunds);
		$this->text = explode('[+]',$this->text);

		if ( $this->config['use_exceptions_list'] == true ){
			$this->text[1] = preg_replace($this->exceptions,$this->e_repl,$this->text[1]);
		}

		if ( $this->config['use_spellcheck'] == true ){	
			$this->debugging .= "<h3>Spell-Checking</h3>";
			$this->text[1] = nl2br($this->spellCheck($this->text[1]));
		} else {
			$this->debugging .= "<h3>Spell-Checking Skipped</h3>";
			$this->text[1] = nl2br($this->text[1]);
		}	
	}

	public function setConfig($propsArray){
		while (list($key,$val) = each($propsArray)){
			$this->config[$key] = $val;
		}
	}
}
?>