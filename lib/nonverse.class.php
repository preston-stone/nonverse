<?php
error_reporting(E_ERROR);
/**
 * Nonsensical verse generator. This app parses mad libs-style templates and replaces parts of speech with relevant
 * words from a database. Currently, this app requires Enchant for advanced functionality.
 *
 * To-do: add spellcheck method that allows choice of spellcheck engine; convert to factory pattern?
 */
class Nonverse {
    public $tmpl;
    public $dbname = 'lexicon.default.db';
    public $db;
    public $debugging = array( 'spellchecker' => '', 'words' => array() );
    public $text;
    public $props;
    public $config = array (
        'match_word_endings' => true,
        'permit_proper_nouns' => true,
        'use_exceptions_list' => true,
        'use_spellcheck' => true,
        //'spellcheck_engine' => 'PSpell',
        'spellcheck_dictionary' => "en_US",
        'use_gerund_replacement' => true,
        'spellcheck_levenshtein_distance' => 5
    );
    private $tpldata;
    private $dkey;
    protected $exceptions = array ("/seing/i",
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
        "/chinese/i",
        "/([^aeiou])ys([^a-zA-Z])/i",
        "/sss/i",
        "/childs/i"
    );
    protected $e_repl = array ("seeing",
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
        "Chinese",
        "$1ies$2",
        "sses",
        "children"
    );

    /**
     * Class constructor - initializes basic variables.
     * @param string $tmpl template to use
     * @param string $dbname database to use
     * @param array $props custom properties to pass to class
     */
    public function __construct($tmpl, $dbname = '',$props = ''){
        $this->tmpl = filter_var($tmpl,FILTER_SANITIZE_STRING);

        if ( $dbname != ''){ 
            $this->dbname = 'lexicon.' . $dbname . '.db'; 
        }
        $this->db = new PDO('sqlite:'.$this->dbname);

        if (is_array($props) ){
            $this->setConfig($props);
        }
    }

    /**
	 * Fetches template content
	 */
    protected function openTemplate(){
        $fp = fopen("tmpl/".$this->tmpl.".tmpl",'r');
        $this->tpldata = fread($fp,filesize("tmpl/$this->tmpl.tmpl"));
        fclose($fp);
    }

    /**
	 * Spell checks parsed template content
     * @param string $string content to spell check
     * @return string spell-checked word
	 */
    protected function spellCheck($string){

        if ( function_exists('enchant_broker_init') && (@$this->config['spellcheck_engine'] == 'enchant' || empty($this->config['spellcheck_engine'])) ){
            $enchant = enchant_broker_init();
            $spell = enchant_broker_request_dict($enchant, $this->config['spellcheck_dictionary']);
            $spellcheck = 'enchant_dict_check';
            $suggest = 'enchant_dict_suggest';
            $this->debugging['spellchecker'] = 'Enchant';
        } elseif ( function_exists('pspell_new') && (@$this->config['spellcheck_engine'] == 'pspell'|| empty($this->config['spellcheck_engine'])) ){
            $spell = pspell_new($this->config['spellcheck_dictionary'], "", "", "",(PSPELL_FAST|PSPELL_RUN_TOGETHER));
            $spellcheck = 'pspell_check';
            $suggest = 'pspell_suggest';
            $this->debugging['spellchecker'] = 'PSpell';
        } else {
            //TODO: PHP spellcheck class
        }
        preg_match_all("/[&;A-Za-z]{1,16}/i", $string, $words);

        for ($i = 0; $i < count($words[0]); $i++) {
            if ( !preg_match("/&([a-zA-Z0-9]+);/",$words[0][$i]) ){
                if (!$spellcheck($spell, $words[0][$i])) {
                    $this->dkey = sizeof($this->debugging['words']);
                    $this->debugging['words'][$this->dkey]['word'] = $words[0][$i];
                    $suggestions = $suggest($spell,$words[0][$i]);
                    shuffle($suggestions);
                    $slist = implode(", ",$suggestions);
                    $this->debugging['words'][$this->dkey]['options'] = $slist;
                    $repl = $this->getBestSuggestion($words[0][$i],$suggestions);        
                    $string = preg_replace("/\b" . $words[0][$i] . "\b/i", '<span class="spellcheck" data-orig="' . $words[0][$i] . '">' . $repl . '</span>', $string);    
                } 
            }
        }
        return $string;    
    }

    /**
	 * Find best option from suggested spelling replacements
	 * @param string $misspelling misspelled word
     * @param array $suggestions word replacement suggestions
     * @param array $props custom properties to pass to class
     * @return string best suggestion
	 */
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

        foreach ($suggestions as $suggestion) {        
            $word_ending = ($this->config['match_word_endings'] == true) ? $this->checkEnding($word,$suggestion) : true;
            
            if (ctype_lower($suggestion[0]) == true && !preg_match("/[\s]/i",$suggestion) && !preg_match("/[-,\.:\&;]/i",$suggestion) && preg_match("/[aeiou]/i",$suggestion ) && $word_ending == true ) {
                $lev = levenshtein($word, $suggestion);
                $this->debugging['words'][$this->dkey]['suggestions'][] = array ('suggestion' => $suggestion, 'levenshtein' => $lev);
                
                if ($lev < $shortest  ) {
                    $match = $suggestion;
                    $shortest = $lev;    
                }
            }            
        }
        $this->debugging['words'][$this->dkey]['match'] = $match;
        return $match;    
    }

    /**
     * Converts verb to gerund form
     * @param string $w word to convert
     * @return string converted gerund
     * @deprecated
     */
    private function gerund($w){
        $word = stripslashes($w);
        $lastchar = strlen($word) - 1;
        if ($word{$lastchar} == 'e' && substr($word,-1,2) != 'ee' && $word != 'be'){
            $word = substr($word,0,$lastchar);
        } elseif ($word{$lastchar} == 't' && preg_match("/[aeiou]/i",$word{$lastchar-1})) {
            $word .= 't';
        }
        $word .= 'ing';
        $this->debugging['words'][$this->dkey]['gerund'] = $word;
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
        $workingtext = $this->tpldata;
        preg_match_all('/<word([^>]+)>/i',$workingtext,$tags,PREG_OFFSET_CAPTURE);
        $temptags = $tags[0];

        for( $i = 0; $i < sizeof($temptags);$i++ ){
            $tag = trim(rtrim(preg_replace("/<word([^>]+)>/i",'$1',$temptags[$i][0]),'/'));
            $tag = explode(" ",$tag);
            

            for ($n = 0; $n < sizeof($tag); $n++){
                list($k,$v) = explode('=',$tag[$n]);
                $v = preg_replace("/([\"])/i",'',$v);
	            $tag[$k] = $v;
                unset($tag[$n]);
            }
            $tag['strpos'] = $temptags[$i][1];
            $poskey = $tag['class'];
            $this->tags[$poskey][] = $tag;
        }
        $counts = $queries = array();
        $parts = array_keys($this->tags);

        foreach($parts as $part){
            $counts[$part] = sizeof($this->tags[$part]);
            $qpos = ( $part == 'gerund' ) ? 'verb' : $part;
            $queries[$part] = "SELECT word FROM lexicon WHERE type = '$qpos' ORDER BY random() LIMIT 0,${counts[$part]}";
        }

        while ( list($pos,$query) = each($queries)){
            $q = $this->db->query($query) or die($query . ' ' . $this->db->error);

            foreach ($q as $r){
            	$pkey = ( isset($pkey) ? sizeof($replacements[$pos]) : 0 );
            	$attrs = $this->tags[$pos][$pkey];
            	$replacements[$pos][] = $this->parseAttrs($r['word'], $attrs);
            	unset($attrs);
            	unset($tagset);
            }
            $subrepl = $replacements[$pos];
            $workingtext = preg_replace_callback( "/<word [^>]+".$pos."[^>]+>/i", function($matches) use (&$subrepl){
				return array_shift($subrepl);          	
            }, $workingtext);
        }
        $this->text = explode('[+]',$workingtext);
        
        if ( $this->config['use_exceptions_list'] == true ){
            $this->text[1] = preg_replace($this->exceptions,$this->e_repl,$this->text[1]);
        }

        if ( @$this->config['use_spellcheck'] == true ){    
            $this->text[0] = $this->spellCheck($this->text[0]);
            $this->text[1] = nl2br($this->spellCheck($this->text[1]));
        } else {
            $this->text[1] = nl2br($this->text[1]);
        }  
    }

    private function parseAttrs($word, $attrs){
        $pos = $attrs['class'];

        switch ($pos){
            case 'noun':
                if ( isset($attrs['data-num']) ){
                    if ( $attrs['data-num'] == 'pl' ){
                        $pword = $word . 's';
                    }
                }
                break;
            case 'verb':
                if ( isset ($attrs['data-tense']) ){
                    if ( $attrs['data-tense'] == 'past'){
                        $suf = ( preg_match("/([aeiou]$)/i",$word)) ? 'd' : 'ed';
                        $pword = $word . $suf;
                    } elseif ( $attrs['data-tense'] == 'present' && $attrs['data-num'] !== 'pl' ){
                    	if ( preg_match( "/([aiou])$/i",$word ) ){
                    		$pword = $word . 'es';
                    	} else {
	                        $pword = $word . 's';
	                    }
                    }
                } elseif (!isset($attrs['data-tense']) && $attrs['data-num'] !== 'pl'){
                	if ( preg_match( "/([aiou])$/i",$word ) ){
                    		$pword = $word . 'es';
                    	} else {
	                        $pword = $word . 's';
	                    }
                }

                if ( isset ( $attrs['data-num'] ) ){
                    if ( $attrs['data-num'] !== 'pl' && ( $attrs['data-tense'] == 'present' || !isset($attrs['data-tense']) ) ){
                        $pword = $word;
                    }
                }
                break;
            case 'gerund':
                $pword = $word .'ing';
                break;
            default:
            	$pword = $word;
        }
        if ( !isset($pword) ){ $pword = $word; }
        return $pword;
    }
    
    public function setConfig($propsArray){
        while (list($key,$val) = each($propsArray)){
            $this->config[$key] = $val;
        }
    }

    public function debug(){
        print_r($this->debugging);
    }
}
?>