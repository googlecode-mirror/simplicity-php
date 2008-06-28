<?php

class smp_String
{

  static function camelize ($word)
  {

    $word = strtolower($word);
    $word = str_replace(' ', '_', $word);
    $word = str_replace('_', ' ', $word);
    $word = ucwords($word);
    $word = str_replace(' ', '', $word);
    return $word;
  }

  static function underscore ($word)
  {

    $word = str_replace(' ', '', $word);
    $word = preg_replace('/([A-Z]{1})/', '_$1', $word);
    $word = strtolower($word);
    if (substr($word, 0, 1) == '_')
      $word = substr($word, 1);
    return $word;
  }

}
?>