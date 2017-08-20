<?php


namespace Chencha\Pspell\Mapping;

use Illuminate\Support\Collection;

class PspellConfigurationMapping implements ConfigurationMapping
{
    protected $mapping = [
        'autosuggest::pspell.run_together' => 'pspell_config_runtogether',
        'autosuggest::pspell.replacement_words' => 'pspell_config_repl',
        'autosuggest::pspell.custom_word_list_file' => 'pspell_config_personal',
        'autosuggest::pspell.number_suggestions' => 'pspell_config_mode',
        'autosuggest::pspell.minimum_word_length' => 'pspell_config_ignore',
    ];

    /**
     * @return Collection
     */
    public function getMapping()
    {
        return Collection::make($this->mapping);
    }

} 