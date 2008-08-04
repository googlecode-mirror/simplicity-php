<?php

class smp_String
{

  const COLOR_DEFAULT = '000';
  const COLOR_FUNCTION = '00b';
  const COLOR_KEYWORD = '070';
  const COLOR_COMMENT = '800080';
  const COLOR_STRING = 'd00';
	
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
  
  function highlight ($code) {

    // Check it if code starts with PHP tags, if not: add 'em.
    if(substr($code, 0, 2) != '<?') {
        $code = "<?\n".$code."\n?>";
        $add_tags = true;
    }
    
    $code = highlight_string($code, true);

    // Remove the first "<code>" tag from "$code" (if any)
    if(substr($code, 0, 6) == '<code>') {
       $code = substr($code, 6, (strlen($code) - 13));
    }

    // Replacement-map to replace deprecated "<font>" tag with "<span>"
    $xhtml_convmap = array(
       '<font' => '<span',
       '</font>' => '</span>',
       'style="color:' => 'style="color:',
       '<br />' => '<br/>',
       '#000000">' => '#'.COLOR_DEFAULT.'">',
       '#0000BB">' => '#'.COLOR_FUNCTION.'">',
       '#007700">' => '#'.COLOR_KEYWORD.'">',
       '#FF8000">' => '#'.COLOR_COMMENT.'">',
       '#DD0000">' => '#'.COLOR_STRING.'">'
    );

    // Replace "<font>" tags with "<span>" tags, to generate a valid XHTML code
    $code = strtr($code, $xhtml_convmap);

    //strip default color (black) tags
    $code = substr($code, 25, (strlen($code) -33));

    //strip the PHP tags if they were added by the script
    if($add_tags) {
        
        $code = substr($code, 0, 26).substr($code, 36, (strlen($code) - 74));
    }

    return $code;
  }
}
?>