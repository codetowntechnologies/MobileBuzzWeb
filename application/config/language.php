<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// instead "dutch" you can also use "nl_NL" ("english" > "en_UK") etc.
// don't forget to change the folder names in languages also
// if you want to use this also as http://domain.com/nl_NL/...
// don't forget to change the router (\w{2}) to (\w{2}_\w{2})
// of course if you change, make your change in this config file also

// default language

#$config['language'] = 'dutch';
$config['language']	= 'german';

/* default language abbreviation */
$config['language_abbr'] = "en";

/* set available language abbreviations */
$config['lang_uri_abbr'] = array("gm" => "german", "en" => "english" , "sp" => "spanish");

/* hide the language segment (use cookie) */
$config['lang_ignore'] = TRUE; 