<?php
/**
 * Created by PhpStorm.
 * User: jacob
 * Date: 26/11/14
 * Time: 20:08
 */

namespace Chencha\Pspell;


class Config
{
    /**
     * This function determines whether run-together words will be treated as legal
     * compounds. That is; "thecat" will be a legal compound; although there should be
     * a space between the two words
     */
    protected $run_together = false;
    /**
     * Set a file that contains replacement pairs.
     * This is particularly useful for domain specific terminologies
     */
    protected $replacement_words = null;
    /**
     * Set a file that contains personal wordlist. The personal wordlist will be loaded
     * and used in addition to the standard one
     */
    protected $custom_word_list_file = null;
    /**
     * Number of suggestions returned available modes are
     * PSPELL_FAST - Fast mode (least number of suggestions)
     * PSPELL_NORMAL - Normal mode (more suggestions)
     * PSPELL_BAD_SPELLERS - Slow mode (a lot of suggestions)
     */
    protected $number_suggestions = 2;
    /**
     * Ignore words less than N characters long
     *
     */
    protected $minimum_word_length = 1;

    //Configurations used to open a dictionary

    /**
     * Dictionary that pspell uses. Must exist
     */
    protected $dictionary = 'en';
    /**
     * The language parameter is the language code which consists of the two letter ISO
     * 639 language code and an optional two letter ISO 3166 country code after a dash or
     * underscore.
     */
    protected $language = 'en';

    /**
     * @param null $custom_word_list_file
     * @param string $dictionary
     * @param string $language
     * @param int $minimum_word_length
     * @param int $number_suggestions
     * @param null $replacement_words
     * @param bool $run_together
     */
    function __construct(
        $custom_word_list_file = null,
        $dictionary = "en",
        $language = "en",
        $minimum_word_length = 1,
        $number_suggestions = 2,
        $replacement_words = null,
        $run_together = false
    ) {
        $this->custom_word_list_file = $custom_word_list_file;
        $this->dictionary = $dictionary;
        $this->language = $language;
        $this->minimum_word_length = $minimum_word_length;
        $this->number_suggestions = $number_suggestions;
        $this->replacement_words = $replacement_words;
        $this->run_together = $run_together;
    }

    function get($item)
    {
        if (isset($this->$item)) {
            return $this->$item;
        }
    }

} 