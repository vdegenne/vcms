<?php

namespace vdegenne;

class Lang {

  /**
   * @param array|null $amongst
   * @return bool|mixed
   */
  static function get_prefered_language (Array $amongst = null) {

    /*
     * http://www.thefutureoftheweb.com/blog/use-accept-language-header
     */
    $langs = array();

    if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
      // break up string into pieces (languages and q factors)
      preg_match_all(
      '/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i',
      $_SERVER['HTTP_ACCEPT_LANGUAGE'], $lang_parse
      );

      if (count($lang_parse[1])) {
        // create a list like "en" => 0.8
        $langs = array_combine($lang_parse[1], $lang_parse[4]);

        // set default to 1 for any without q factor
        foreach ($langs as $lang => $val) {
          if ($val === '') {
            $langs[$lang] = 1;
          }
        }

        // sort list based on value
        arsort($langs, SORT_NUMERIC);
      }
    }


    if (is_null($amongst)) {
      reset($langs);

      return key($langs);
    }


    foreach ($langs as $lang => $value) {

      foreach ($amongst as $prefLang) {
        if (strpos($lang, $prefLang) === 0) {
          return $prefLang;
        }
      }
    }


    // Aucun langage préféré parmis le tableau en arguments.
    return false;
  }
}