<?php

$input = "
<h2 class=\"shop-front-partof\">Cession entreprise et Bail - 4 Heures - Carte T</h2>
<p><strong>Objectifs:</strong></p><p>En suivant cette formation, l’apprenant a pour objectif de connaitre&nbsp; l’impact juridique et fiscal des différentes catégories de bail lors de la cession du fonds de commerce ou de la société commerciale.</p><p><strong>Compétences visées:</strong></p><p>Maîtriser les différentes formes de bail , élément d'actif incorporel stratégique de l'entreprise.</p><p>Comprendre l'importance des différentes clauses de la convention de garantie d'actif et de passif.</p><p><strong>Eligible Obligation de formation Carte T Loi Alur.</strong></p>
";

echo htmlentities($input);

$n = @$_REQUEST['n'];
if (empty($n)) {
    $n = 500;
} else {
    echo "<br/>Working with n = {$n}<br/>\n";
}

echo trim_chars($input, $n);

/**
 * Cut a text to some length.
 *
 * @param $str
 * @param $n
 * @param $end_char
 * @return string
 */
function trim_chars($str, $n = 500, $endchar = '...') {

    $debug = $_REQUEST['debug'];
    if ($debug) {
        echo '<pre>';
    }

    $opentags = [];
    $singletags = ['br', 'hr', 'img', 'input', 'link'];

    if (empty($str)) {
        // Optimize return.
        return '';
    }

    // Tokenize around HTML tags.
    $htmltagpattern = "#(.*?)(</?[^>]+?>)(.*)#s";
    $end = $str;
    $parts = [];
    while (preg_match($htmltagpattern, $end, $matches)) {
        echo htmlentities(print_r($matches, true));
        if (!empty($matches[1])) {
            array_push($parts, $matches[1]);
        }
        if (!empty($matches[2])) {
            array_push($parts, $matches[2]);
        }
        $end = $matches[3];
    }
    if (!empty($matches)) {
        // Take last end that has no more tags inside.
        array_push($parts, $end);
    }

    $buflen = 0;
    $buf = '';
    $iscutoff = false;

    if ($debug) {
        echo "Having ".count($parts)." to parse\n";
    }

    while ($part = array_shift($parts)) {

        if ($buflen > $n) {
            $iscutoff = true;
            if ($debug) {
                echo "Cutting off\n";
            }
            break;
        }

        if (strpos($part, '<') === 0) {
            // is a tag.
            preg_match('#<(/?)([a-zA-Z0-6]+).*(/?)>#', $part, $matches);
            $isendtag = !empty($matches[1]);
            $tagname = $matches[2];
            $issingletag = (!empty($matches[3]) || in_array($tagname, $singletags));
            if (!$issingletag) {
                if (!$isendtag) {
                    // So its a starting tag and NOT single.
                    if ($debug) {
                        echo "Start Tag $tagname\n";
                    }
                    array_push($opentags, $tagname);
                } else {
                    // So its an ending tag. We just check it has been correctly stacked.
                    $lasttag = array_pop($opentags);
                    if ($debug) {
                        echo "End Tag $tagname\n";
                    }
                    if ($lasttag !== $tagname) {
                        // This is a nesting error in the source HTML.
                        throw new moodle_exception("Malformed HTML content somewhere in product descriptions");
                    }
                }
            } else {
                if ($debug) {
                    echo "Single Tag $tagname\n";
                }
            }
        } else {
            // Is text.
            // TODO : cut the text to the remaining amount of chars to get near $n chars.
            $buflen += mb_strlen(str_replace("\n", '', $part));
            if ($debug) {
                echo "Text node '$part': Adding ".mb_strlen(str_replace("\n", '', $part))."\n";
                echo "Buflen : $buflen\n";
            }
        }
        $buf .= $part;
    }

    if (!$iscutoff) {
        if ($debug) {
            echo '</pre>';
        }
        return $str;
    }

    if (!empty($parts)) {
        // Add final ellipsis if there is something retained in original string.
        $buf .= $endchar;
    }

    // At this point, $opentags should be empty if all openedtags have been closed.
    while (!empty($opentags)) {
        $closing = '</'.array_pop($opentags).'>';
        $buf .= $closing;
    }

    if ($debug) {
        echo '</pre>';
    }
    return $buf;
}
