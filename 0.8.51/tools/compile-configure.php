<?php

function minify_html($input)
{
  if (trim($input) === "") return $input;

  // Remove extra white-space(s) between HTML attribute(s)

  $input = preg_replace_callback('#<([^\/\s<>!]+)(?:\s+([^<>]*?)\s*|\s*)(\/?)>#s',
  function ($matches)
  {
    return '<' . $matches[1] . preg_replace('#([^\s=]+)(\=([\'"]?)(.*?)\3)?(\s+|$)#s', ' $1$2', $matches[2]) . $matches[3] . '>';
  }

  , str_replace("\r", "", $input));

  // Minify inline CSS declaration(s)

  if (strpos($input, ' style=') !== false) {
    $input = preg_replace_callback('#<([^<]+?)\s+style=([\'"])(.*?)\2(?=[\/\s>])#s',
    function ($matches)
    {
      return '<' . $matches[1] . ' style=' . $matches[2] . minify_css($matches[3]) . $matches[2];
    }

    , $input);
  }

  return preg_replace(array(

    // t = text
    // o = tag open
    // c = tag close
    // Keep important white-space(s) after self-closing HTML tag(s)

    '#<(img|input)(>| .*?>)#s',

    // Remove a line break and two or more white-space(s) between tag(s)

    '#(<!--.*?-->)|(>)(?:\n*|\s{2,})(<)|^\s*|\s*$#s',
    '#(<!--.*?-->)|(?<!\>)\s+(<\/.*?>)|(<[^\/]*?>)\s+(?!\<)#s', // t+c || o+t
    '#(<!--.*?-->)|(<[^\/]*?>)\s+(<[^\/]*?>)|(<\/.*?>)\s+(<\/.*?>)#s', // o+o || c+c
    '#(<!--.*?-->)|(<\/.*?>)\s+(\s)(?!\<)|(?<!\>)\s+(\s)(<[^\/]*?\/?>)|(<[^\/]*?\/?>)\s+(\s)(?!\<)#s', // c+t || t+o || o+t -- separated by long white-space(s)
    '#(<!--.*?-->)|(<[^\/]*?>)\s+(<\/.*?>)#s', // empty tag
    '#<(img|input)(>| .*?>)<\/\1\x1A>#s', // reset previous fix
    '#(&nbsp;)&nbsp;(?![<\s])#', // clean up ...

    // Force line-break with `&#10;` or `&#xa;`

    '#&\#(?:10|xa);#',

    // Force white-space with `&#32;` or `&#x20;`

    '#&\#(?:32|x20);#',

    // Remove HTML comment(s) except IE comment(s)

    '#\s*<!--(?!\[if\s).*?-->\s*|(?<!\>)\n+(?=\<[^!])#s'
  ) , array(
    "<$1$2</$1\x1A>",
    '$1$2$3',
    '$1$2$3',
    '$1$2$3$4$5',
    '$1$2$3$4$5$6$7',
    '$1$2$3',
    '<$1$2',
    '$1 ',
    "\n",
    ' ',
    ""
  ) , $input);
}

// CSS Minifier => http://ideone.com/Q5USEF + improvement(s)

function minify_css($input)
{
  if (trim($input) === "") return $input;

  // Force white-space(s) in `calc()`

  if (strpos($input, 'calc(') !== false) {
    $input = preg_replace_callback('#(?<=[\s:])calc\(\s*(.*?)\s*\)#',
    function ($matches)
    {
      return 'calc(' . preg_replace('#\s+#', "\x1A", $matches[1]) . ')';
    }

    , $input);
  }

  return preg_replace(array(

    // Remove comment(s)

    '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')|\/\*(?!\!)(?>.*?\*\/)|^\s*|\s*$#s',

    // Remove unused white-space(s)

    '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'|\/\*(?>.*?\*\/))|\s*+;\s*+(})\s*+|\s*+([*$~^|]?+=|[{};,>~+]|\s*+-(?![0-9\.])|!important\b)\s*+|([[(:])\s++|\s++([])])|\s++(:)\s*+(?!(?>[^{}"\']++|"(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')*+{)|^\s++|\s++\z|(\s)\s+#si',

    // Replace `0(cm|em|ex|in|mm|pc|pt|px|vh|vw|%)` with `0`

    '#(?<=[\s:])(0)(cm|em|ex|in|mm|pc|pt|px|vh|vw|%)#si',

    // Replace `:0 0 0 0` with `:0`

    '#:(0\s+0|0\s+0\s+0\s+0)(?=[;\}]|\!important)#i',

    // Replace `background-position:0` with `background-position:0 0`

    '#(background-position):0(?=[;\}])#si',

    // Replace `0.6` with `.6`, but only when preceded by a white-space or `=`, `:`, `,`, `(`, `-`

    '#(?<=[\s=:,\(\-]|&\#32;)0+\.(\d+)#s',

    // Minify string value

    '#(\/\*(?>.*?\*\/))|(?<!content\:)([\'"])([a-z_][-\w]*?)\2(?=[\s\{\}\];,])#si',
    '#(\/\*(?>.*?\*\/))|(\burl\()([\'"])([^\s]+?)\3(\))#si',

    // Minify HEX color code

    '#(?<=[\s=:,\(]\#)([a-f0-6]+)\1([a-f0-6]+)\2([a-f0-6]+)\3#i',

    // Replace `(border|outline):none` with `(border|outline):0`

    '#(?<=[\{;])(border|outline):none(?=[;\}\!])#',

    // Remove empty selector(s)

    '#(\/\*(?>.*?\*\/))|(^|[\{\}])(?:[^\s\{\}]+)\{\}#s',
    '#\x1A#'
  ) , array(
    '$1',
    '$1$2$3$4$5$6$7',
    '$1',
    ':0',
    '$1:0 0',
    '.$1',
    '$1$3',
    '$1$2$4$5',
    '$1$2$3',
    '$1:0',
    '$1$2',
    ' '
  ) , $input);
}

// JavaScript Minifier

function minify_js($input)
{
  if (trim($input) === "") return $input;
  return preg_replace(array(

    // Remove comment(s)

    '#\s*("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')\s*|\s*\/\*(?!\!|@cc_on)(?>[\s\S]*?\*\/)\s*|\s*(?<![\:\=])\/\/.*(?=[\n\r]|$)|^\s*|\s*$#',

    // Remove white-space(s) outside the string and regex

    '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'|\/\*(?>.*?\*\/)|\/(?!\/)[^\n\r]*?\/(?=[\s.,;]|[gimuy]|$))|\s*([!%&*\(\)\-=+\[\]\{\}|;:,.<>?\/])\s*#s',

    // Remove the last semicolon

    '#;+\}#',

    // Minify object attribute(s) except JSON attribute(s). From `{'foo':'bar'}` to `{foo:'bar'}`

    '#([\{,])([\'])(\d+|[a-z_]\w*)\2(?=\:)#i',

    // --ibid. From `foo['bar']` to `foo.bar`

    '#([\w\)\]])\[([\'"])([a-z_]\w*)\2\]#i',

    // Replace `true` with `!0`

    '#(?<=return |[=:,\(\[])true\b#',

    // Replace `false` with `!1`

    '#(?<=return |[=:,\(\[])false\b#',

    // Clean up ...

    '#\s*(\/\*|\*\/)\s*#'
  ) , array(
    '$1',
    '$1$2',
    '}',
    '$1$3',
    '$1.$3',
    '!0',
    '!1',
    '$1'
  ) , $input);
}

function _copyFolder($src, $tgt)
{  
  foreach(glob("$src/*") as $filename) {
  	if (is_dir($filename))
      _copyFolder($filename, $tgt."/".basename($filename));
    else {
      if (!is_dir($tgt)) {
      	echo "Making dir '$tgt'\n";
      	mkdir($tgt, 0777, true);
      }
      copy($filename, "$tgt/".basename($filename));
    }

  }
}

require_once "includes/yeapf.functions.php";

chdir("app-src");
$config_php = minify_html(_file("configure.php"));
file_put_contents("../skel/webApp/config2.php", $config_php);

chdir("i_configure");
/*
_copyFolder("css",   "../../skel/webApp/css");
_copyFolder("fonts", "../../skel/webApp/fonts");
_copyFolder("js",    "../../skel/webApp/js");
*/

_copyFolder("img",   "../../skel/webApp/img");
?>