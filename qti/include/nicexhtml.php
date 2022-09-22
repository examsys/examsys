<?php

// This file is part of ExamSys
//
// ExamSys is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// ExamSys is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with ExamSys.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * @author Adam Clarke
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

NX_CreateQTI2Tree();

function MakeNiceXHTML($string, $starttag = 'itemBody')
{

    $string = NX_ChangePreSetChars($string);
    $xmlStr = "<?xml version='1.0' encoding='UTF-8' standalone='yes'?>\n<$starttag><p>\n$string\n</p></$starttag>\n";
    /*echo "<pre>";
    echo "\n\nINPUT\n\n";
    echo htmlentities($xmlStr);
    echo "</pre>";*/

    $xml = simplexml_load_string($xmlStr);
    $xml = dom_import_simplexml($xml);

    //echo "XML Parsing - <br>";
    while (!NX_ValidateXML($xml)) {
        /*echo "AFTER PASSE<br>";
        $Document = new DOMDocument();
        $Document->appendChild($Document->importNode($xml,true));
        echo "<pre>";
        echo htmlentities($Document->saveHTML());
        echo "</pre>";*/
    }

    $Document = new DOMDocument();
    $Document->appendChild($Document->importNode($xml, true));
    $string = $Document->saveHTML();
    $string = str_replace("<$starttag>", '', $string);
    $string = str_replace("</$starttag>", '', $string);
    $string = NX_ChangePreSetChars($string);
    $string = str_ireplace('<br>', '<br />', $string);
    /*echo "<pre>";
    echo "\nOUTPUT\n\n";
    echo htmlentities($string);
    echo "</pre>";*/

    //$string = NX_StripBadTags($string);

    return $string;
}

function NX_IsValidAttr($type, $attr)
{
    global $tree;

    foreach ($tree[$type]['attr'] as $attrib) {
        if ($attr == $attrib) {
            return true;
        }
    }
    return false;
}

function NX_IsValidSubnode($type, $node)
{
    global $tree;

    foreach ($tree[$type]['tags'] as $tag) {
        if ($node == $tag) {
            return true;
        }
    }
    return false;
}

function NX_ValidateXML(&$xml)
{
    //echo "ITEM : " . $xml->tagName . "<br>ATTRIBUTES:";

    //strip any invalid attributes
    if ($xml->hasAttributes()) {
        foreach ($xml->attributes as $attribute) {
            if (!NX_IsValidAttr($xml->tagName, $id)) {
                //echo "INVALID ATTRIBUTE : " . $attribute->name . "<Br>";
                $xml->removeAttribute($attribute->name);
            }
        }
    }

    //echo "<br>CHILDREN<br>";
    if (count($xml->childNodes) > 0) {
        foreach ($xml->childNodes as $item) {
            if ($item->tagName == '') {
                continue;
            }

            //echo "Child node " . $item->tagName . " under element " . $xml->tagName . "<br>";
            if (NX_IsValidSubnode($xml->tagName, $item->tagName)) {
                if (!NX_ValidateXML($item)) {
                    //echo "FASLE<br>";
                    return false;
                }
            } else {
                //echo "Invalid Child node " . $item->tagName . " under element " . $xml->tagName . "<br>";

                // attempt to put child node in the parent node if it fits
                $parnode = $item->parentNode;
                $lastparent = $parnode;
                $fitted = false;
                $count = 0;
                while ($count < 20) {
                    $lastparent = $parnode;
                    $parnode = $parnode->parentNode;

                    if ($parnode->tagName == '') {
                        break;
                    }

                    //echo "Parent Node : " . $parnode->tagName . "<br>";
                    if (NX_IsValidSubnode($parnode->tagName, $item->tagName)) {
                        //echo "Child node " . $item->tagName . " can be put under parent node " . $parnode->tagName . " after tag " . $lastparent->tagName . "<br>";

                        /*if($lastparent->nextSibling) {
                        $parnode->insertBefore($item->cloneNode(true), $lastparent->nextSibling);
                        } else {
                        $parnode->appendChild($item->cloneNode(true));
                        }


                        //$parnode->insertBefore($item->cloneNode(true),$lastparent->nextChild);
                        $xml->removeChild($item);*/
                        $fitted = true;
                    }

                    $count++;
                }

                if ($fitted) {
                    $before = $xml->cloneNode();
                    $after = $xml->cloneNode();

                    $back = $item;
                    while ($back->previousSibling) {
                        //echo "Moving node before " . $back->previousSibling->tagName . "<br>";
                        if ($before->hasChildNodes()) {
                            //echo "insertBefore<br>";
                            $before->insertBefore($back->previousSibling->cloneNode(true), $before->firstChild);
                        } else {
                            //echo "appendChild<br>";
                            $before->appendChild($back->previousSibling->cloneNode(true));
                        }
                        $back = $back->previousSibling;
                    }

                    $forw = $item;
                    while ($forw->nextSibling) {
                        //echo "Moving node after " . $forw->nextSibling->tagName . "<br>";
                        //echo "appendChild<br>";
                        $after->appendChild($forw->nextSibling->cloneNode(true));
                        $forw = $forw->nextSibling;
                    }

                    /*echo "BEFORE BAD NODE<br>";
                    $Document = new DOMDocument();
                    $Document->appendChild($Document->importNode($before,true));
                    echo "<pre>";
                    echo htmlentities($Document->saveHTML());
                    echo "</pre>";*/

                    /*echo "BAD NODE<br>";
                    $Document = new DOMDocument();
                    $Document->appendChild($Document->importNode($item,true));
                    echo "<pre>";
                    echo htmlentities($Document->saveHTML());
                    echo "</pre>";*/

                    /*echo "AFTER BAD NODE<br>";
                    $Document = new DOMDocument();
                    $Document->appendChild($Document->importNode($after,true));
                    echo "<pre>";
                    echo htmlentities($Document->saveHTML());
                    echo "</pre>";*/

                    $parnode = $xml->parentNode;
                    $parnode->insertBefore($before, $xml);
                    $parnode->insertBefore($item, $xml);
                    $parnode->insertBefore($after, $xml);
                    $parnode->removeChild($xml);

                    /*echo "FINAL<br>";
                    $Document = new DOMDocument();
                    $Document->appendChild($Document->importNode($parnode,true));
                    echo "<pre>";
                    echo htmlentities($Document->saveHTML());
                    echo "</pre>";*/

                    return false;
                }

                if (!$fitted) {
                    /*foreach($item->childNodes as $itemchild)
                    {
                    //$item->removeChild($itemchild);
                    //echo "Inserting sub node " . $itemchild->tagName . " (" . $itemchild->wholeText . ")<br>";
                    $xml->insertBefore($itemchild->cloneNode(true),$item);
                    }*/

                    $xml->removeChild($item);
                }
                //echo "FASLE<br>";
                return false;
            }
        }
    }

    //echo "TRUE<br>";
    return true;
}

function NX_StripBadTags($string)
{
    return strip_tags($string, '<pre><h1><h2><h3><h4><h5><h6><p><dl><ol><hr><ul><table><div><dd><dt><li><caption><col><colgroup><thead><tfoot><tr><td><th>');
}

function NX_ChangePreSetChars($string)
{
    $chars['&amp;'] = '&#38;';
    $chars['&gt;'] = '&#62;';
    $chars['&lt;'] = '&#60;';
    $chars['&quot;'] = '&#34;';
    $chars['&acute;'] = '&#180;';
    $chars['&cedil;'] = '&#184;';
    $chars['&circ;'] = '&#710;';
    $chars['&macr;'] = '&#175;';
    $chars['&middot;'] = '&#183;';
    $chars['&tilde;'] = '&#732;';
    $chars['&uml;'] = '&#168;';
    $chars['&Aacute;'] = '&#193;';
    $chars['&aacute;'] = '&#225;';
    $chars['&Acirc;'] = '&#194;';
    $chars['&acirc;'] = '&#226;';
    $chars['&AElig;'] = '&#198;';
    $chars['&aelig;'] = '&#230;';
    $chars['&Agrave;'] = '&#192;';
    $chars['&agrave;'] = '&#224;';
    $chars['&Aring;'] = '&#197;';
    $chars['&aring;'] = '&#229;';
    $chars['&Atilde;'] = '&#195;';
    $chars['&atilde;'] = '&#227;';
    $chars['&Auml;'] = '&#196;';
    $chars['&auml;'] = '&#228;';
    $chars['&Ccedil;'] = '&#199;';
    $chars['&ccedil;'] = '&#231;';
    $chars['&Eacute;'] = '&#201;';
    $chars['&eacute;'] = '&#233;';
    $chars['&Ecirc;'] = '&#202;';
    $chars['&ecirc;'] = '&#234;';
    $chars['&Egrave;'] = '&#200;';
    $chars['&egrave;'] = '&#232;';
    $chars['&ETH;'] = '&#208;';
    $chars['&eth;'] = '&#240;';
    $chars['&Euml;'] = '&#203;';
    $chars['&euml;'] = '&#235;';
    $chars['&Iacute;'] = '&#205;';
    $chars['&iacute;'] = '&#237;';
    $chars['&Icirc;'] = '&#206;';
    $chars['&icirc;'] = '&#238;';
    $chars['&Igrave;'] = '&#204;';
    $chars['&igrave;'] = '&#236;';
    $chars['&Iuml;'] = '&#207;';
    $chars['&iuml;'] = '&#239;';
    $chars['&Ntilde;'] = '&#209;';
    $chars['&ntilde;'] = '&#241;';
    $chars['&Oacute;'] = '&#211;';
    $chars['&oacute;'] = '&#243;';
    $chars['&Ocirc;'] = '&#212;';
    $chars['&ocirc;'] = '&#244;';
    $chars['&OElig;'] = '&#338;';
    $chars['&oelig;'] = '&#339;';
    $chars['&Ograve;'] = '&#210;';
    $chars['&ograve;'] = '&#242;';
    $chars['&Oslash;'] = '&#216;';
    $chars['&oslash;'] = '&#248;';
    $chars['&Otilde;'] = '&#213;';
    $chars['&otilde;'] = '&#245;';
    $chars['&Ouml;'] = '&#214;';
    $chars['&ouml;'] = '&#246;';
    $chars['&Scaron;'] = '&#352;';
    $chars['&scaron;'] = '&#353;';
    $chars['&szlig;'] = '&#223;';
    $chars['&THORN;'] = '&#222;';
    $chars['&thorn;'] = '&#254;';
    $chars['&Uacute;'] = '&#218;';
    $chars['&uacute;'] = '&#250;';
    $chars['&Ucirc;'] = '&#219;';
    $chars['&ucirc;'] = '&#251;';
    $chars['&Ugrave;'] = '&#217;';
    $chars['&ugrave;'] = '&#249;';
    $chars['&Uuml;'] = '&#220;';
    $chars['&uuml;'] = '&#252;';
    $chars['&Yacute;'] = '&#221;';
    $chars['&yacute;'] = '&#253;';
    $chars['&yuml;'] = '&#255;';
    $chars['&Yuml;'] = '&#376;';
    $chars['&cent;'] = '&#162;';
    $chars['&curren;'] = '&#164;';
    $chars['&euro;'] = '&#8364;';
    $chars['&pound;'] = '&#163;';
    $chars['&yen;'] = '&#165;';
    $chars['&brvbar;'] = '&#166;';
    $chars['&bull;'] = '&#8226;';
    $chars['&copy;'] = '&#169;';
    $chars['&dagger;'] = '&#8224;';
    $chars['&Dagger;'] = '&#8225;';
    $chars['&frasl;'] = '&#8260;';
    $chars['&hellip;'] = '&#8230;';
    $chars['&iexcl;'] = '&#161;';
    $chars['&image;'] = '&#8465;';
    $chars['&iquest;'] = '&#191;';
    $chars['&lrm;'] = '&#8206;';
    $chars['&mdash;'] = '&#8212;';
    $chars['&ndash;'] = '&#8211;';
    $chars['&not;'] = '&#172;';
    $chars['&oline;'] = '&#8254;';
    $chars['&ordf;'] = '&#170;';
    $chars['&ordm;'] = '&#186;';
    $chars['&para;'] = '&#182;';
    $chars['&permil;'] = '&#8240;';
    $chars['&prime;'] = '&#8242;';
    $chars['&Prime;'] = '&#8243;';
    $chars['&real;'] = '&#8476;';
    $chars['&reg;'] = '&#174;';
    $chars['&rlm;'] = '&#8207;';
    $chars['&sect;'] = '&#167;';
    $chars['&shy;'] = '&#173;';
    $chars['&sup1;'] = '&#185;';
    $chars['&trade;'] = '&#8482;';
    $chars['&weierp;'] = '&#8472;';
    $chars['&bdquo;'] = '&#8222;';
    $chars['&laquo;'] = '&#171;';
    $chars['&ldquo;'] = '&#8220;';
    $chars['&lsaquo;'] = '&#8249;';
    $chars['&lsquo;'] = '&#8216;';
    $chars['&raquo;'] = '&#187;';
    $chars['&rdquo;'] = '&#8221;';
    $chars['&rsaquo;'] = '&#8250;';
    $chars['&rsquo;'] = '&#8217;';
    $chars['&sbquo;'] = '&#8218;';
    $chars['&emsp;'] = '&#8195;';
    $chars['&ensp;'] = '&#8194;';
    $chars['&nbsp;'] = '&#160;';
    $chars['&thinsp;'] = '&#8201;';
    $chars['&zwj;'] = '&#8205;';
    $chars['&zwnj;'] = '&#8204;';
    $chars['&deg;'] = '&#176;';
    $chars['&divide;'] = '&#247;';
    $chars['&frac12;'] = '&#189;';
    $chars['&frac14;'] = '&#188;';
    $chars['&frac34;'] = '&#190;';
    $chars['&ge;'] = '&#8805;';
    $chars['&le;'] = '&#8804;';
    $chars['&minus;'] = '&#8722;';
    $chars['&sup2;'] = '&#178;';
    $chars['&sup3;'] = '&#179;';
    $chars['&times;'] = '&#215;';
    $chars['&alefsym;'] = '&#8501;';
    $chars['&and;'] = '&#8743;';
    $chars['&ang;'] = '&#8736;';
    $chars['&asymp;'] = '&#8776;';
    $chars['&cap;'] = '&#8745;';
    $chars['&cong;'] = '&#8773;';
    $chars['&cup;'] = '&#8746;';
    $chars['&empty;'] = '&#8709;';
    $chars['&equiv;'] = '&#8801;';
    $chars['&exist;'] = '&#8707;';
    $chars['&fnof;'] = '&#402;';
    $chars['&forall;'] = '&#8704;';
    $chars['&infin;'] = '&#8734;';
    $chars['&int;'] = '&#8747;';
    $chars['&isin;'] = '&#8712;';
    $chars['&lang;'] = '&#9001;';
    $chars['&lceil;'] = '&#8968;';
    $chars['&lfloor;'] = '&#8970;';
    $chars['&lowast;'] = '&#8727;';
    $chars['&micro;'] = '&#181;';
    $chars['&nabla;'] = '&#8711;';
    $chars['&ne;'] = '&#8800;';
    $chars['&ni;'] = '&#8715;';
    $chars['&notin;'] = '&#8713;';
    $chars['&nsub;'] = '&#8836;';
    $chars['&oplus;'] = '&#8853;';
    $chars['&or;'] = '&#8744;';
    $chars['&otimes;'] = '&#8855;';
    $chars['&part;'] = '&#8706;';
    $chars['&perp;'] = '&#8869;';
    $chars['&plusmn;'] = '&#177;';
    $chars['&prod;'] = '&#8719;';
    $chars['&prop;'] = '&#8733;';
    $chars['&radic;'] = '&#8730;';
    $chars['&rang;'] = '&#9002;';
    $chars['&rceil;'] = '&#8969;';
    $chars['&rfloor;'] = '&#8971;';
    $chars['&sdot;'] = '&#8901;';
    $chars['&sim;'] = '&#8764;';
    $chars['&sub;'] = '&#8834;';
    $chars['&sube;'] = '&#8838;';
    $chars['&sum;'] = '&#8721;';
    $chars['&sup;'] = '&#8835;';
    $chars['&supe;'] = '&#8839;';
    $chars['&there4;'] = '&#8756;';
    $chars['&Alpha;'] = '&#913;';
    $chars['&alpha;'] = '&#945;';
    $chars['&Beta;'] = '&#914;';
    $chars['&beta;'] = '&#946;';
    $chars['&Chi;'] = '&#935;';
    $chars['&chi;'] = '&#967;';
    $chars['&Delta;'] = '&#916;';
    $chars['&delta;'] = '&#948;';
    $chars['&Epsilon;'] = '&#917;';
    $chars['&epsilon;'] = '&#949;';
    $chars['&Eta;'] = '&#919;';
    $chars['&eta;'] = '&#951;';
    $chars['&Gamma;'] = '&#915;';
    $chars['&gamma;'] = '&#947;';
    $chars['&Iota;'] = '&#921;';
    $chars['&iota;'] = '&#953;';
    $chars['&Kappa;'] = '&#922;';
    $chars['&kappa;'] = '&#954;';
    $chars['&Lambda;'] = '&#923;';
    $chars['&lambda;'] = '&#955;';
    $chars['&Mu;'] = '&#924;';
    $chars['&mu;'] = '&#956;';
    $chars['&Nu;'] = '&#925;';
    $chars['&nu;'] = '&#957;';
    $chars['&Omega;'] = '&#937;';
    $chars['&omega;'] = '&#969;';
    $chars['&Omicron;'] = '&#927;';
    $chars['&omicron;'] = '&#959;';
    $chars['&Phi;'] = '&#934;';
    $chars['&phi;'] = '&#966;';
    $chars['&Pi;'] = '&#928;';
    $chars['&pi;'] = '&#960;';
    $chars['&piv;'] = '&#982;';
    $chars['&Psi;'] = '&#936;';
    $chars['&psi;'] = '&#968;';
    $chars['&Rho;'] = '&#929;';
    $chars['&rho;'] = '&#961;';
    $chars['&Sigma;'] = '&#931;';
    $chars['&sigma;'] = '&#963;';
    $chars['&sigmaf;'] = '&#962;';
    $chars['&Tau;'] = '&#932;';
    $chars['&tau;'] = '&#964;';
    $chars['&Theta;'] = '&#920;';
    $chars['&theta;'] = '&#952;';
    $chars['&thetasym;'] = '&#977;';
    $chars['&upsih;'] = '&#978;';
    $chars['&Upsilon;'] = '&#933;';
    $chars['&upsilon;'] = '&#965;';
    $chars['&Xi;'] = '&#926;';
    $chars['&xi;'] = '&#958;';
    $chars['&Zeta;'] = '&#918;';
    $chars['&zeta;'] = '&#950;';
    $chars['&crarr;'] = '&#8629;';
    $chars['&darr;'] = '&#8595;';
    $chars['&dArr;'] = '&#8659;';
    $chars['&harr;'] = '&#8596;';
    $chars['&hArr;'] = '&#8660;';
    $chars['&larr;'] = '&#8592;';
    $chars['&lArr;'] = '&#8656;';
    $chars['&rarr;'] = '&#8594;';
    $chars['&rArr;'] = '&#8658;';
    $chars['&uarr;'] = '&#8593;';
    $chars['&uArr;'] = '&#8657;';
    $chars['&clubs;'] = '&#9827;';
    $chars['&diams;'] = '&#9830;';
    $chars['&hearts;'] = '&#9829;';
    $chars['&spades;'] = '&#9824;';
    $chars['&loz;'] = '&#9674;';

    foreach ($chars as $find => $replace) {
        $string = str_replace($find, $replace, $string);
    }

    return $string;
}

function NX_ChangePreSetCharsToRaw($string)
{
    $chars['&amp;'] = chr(38);
    $chars['&gt;'] = chr(62);
    $chars['&lt;'] = chr(60);
    $chars['&quot;'] = chr(34);
    $chars['&acute;'] = chr(180);
    $chars['&cedil;'] = chr(184);
    $chars['&circ;'] = chr(710);
    $chars['&macr;'] = chr(175);
    $chars['&middot;'] = chr(183);
    $chars['&tilde;'] = chr(732);
    $chars['&uml;'] = chr(168);
    $chars['&Aacute;'] = chr(193);
    $chars['&aacute;'] = chr(225);
    $chars['&Acirc;'] = chr(194);
    $chars['&acirc;'] = chr(226);
    $chars['&AElig;'] = chr(198);
    $chars['&aelig;'] = chr(230);
    $chars['&Agrave;'] = chr(192);
    $chars['&agrave;'] = chr(224);
    $chars['&Aring;'] = chr(197);
    $chars['&aring;'] = chr(229);
    $chars['&Atilde;'] = chr(195);
    $chars['&atilde;'] = chr(227);
    $chars['&Auml;'] = chr(196);
    $chars['&auml;'] = chr(228);
    $chars['&Ccedil;'] = chr(199);
    $chars['&ccedil;'] = chr(231);
    $chars['&Eacute;'] = chr(201);
    $chars['&eacute;'] = chr(233);
    $chars['&Ecirc;'] = chr(202);
    $chars['&ecirc;'] = chr(234);
    $chars['&Egrave;'] = chr(200);
    $chars['&egrave;'] = chr(232);
    $chars['&ETH;'] = chr(208);
    $chars['&eth;'] = chr(240);
    $chars['&Euml;'] = chr(203);
    $chars['&euml;'] = chr(235);
    $chars['&Iacute;'] = chr(205);
    $chars['&iacute;'] = chr(237);
    $chars['&Icirc;'] = chr(206);
    $chars['&icirc;'] = chr(238);
    $chars['&Igrave;'] = chr(204);
    $chars['&igrave;'] = chr(236);
    $chars['&Iuml;'] = chr(207);
    $chars['&iuml;'] = chr(239);
    $chars['&Ntilde;'] = chr(209);
    $chars['&ntilde;'] = chr(241);
    $chars['&Oacute;'] = chr(211);
    $chars['&oacute;'] = chr(243);
    $chars['&Ocirc;'] = chr(212);
    $chars['&ocirc;'] = chr(244);
    $chars['&Ograve;'] = chr(210);
    $chars['&ograve;'] = chr(242);
    $chars['&Oslash;'] = chr(216);
    $chars['&oslash;'] = chr(248);
    $chars['&Otilde;'] = chr(213);
    $chars['&otilde;'] = chr(245);
    $chars['&Ouml;'] = chr(214);
    $chars['&ouml;'] = chr(246);
    $chars['&szlig;'] = chr(223);
    $chars['&THORN;'] = chr(222);
    $chars['&thorn;'] = chr(254);
    $chars['&Uacute;'] = chr(218);
    $chars['&uacute;'] = chr(250);
    $chars['&Ucirc;'] = chr(219);
    $chars['&ucirc;'] = chr(251);
    $chars['&Ugrave;'] = chr(217);
    $chars['&ugrave;'] = chr(249);
    $chars['&Uuml;'] = chr(220);
    $chars['&uuml;'] = chr(252);
    $chars['&Yacute;'] = chr(221);
    $chars['&yacute;'] = chr(253);
    $chars['&yuml;'] = chr(255);
    $chars['&cent;'] = chr(162);
    $chars['&curren;'] = chr(164);
    $chars['&pound;'] = chr(163);
    $chars['&yen;'] = chr(165);
    $chars['&brvbar;'] = chr(166);
    $chars['&copy;'] = chr(169);
    $chars['&iexcl;'] = chr(161);
    $chars['&iquest;'] = chr(191);
    $chars['&not;'] = chr(172);
    $chars['&ordf;'] = chr(170);
    $chars['&ordm;'] = chr(186);
    $chars['&para;'] = chr(182);
    $chars['&reg;'] = chr(174);
    $chars['&sect;'] = chr(167);
    $chars['&shy;'] = chr(173);
    $chars['&sup1;'] = chr(185);
    $chars['&laquo;'] = chr(171);
    $chars['&raquo;'] = chr(187);
    $chars['&nbsp;'] = ' ';
    $chars['&deg;'] = chr(176);
    $chars['&divide;'] = chr(247);
    $chars['&frac12;'] = chr(189);
    $chars['&frac14;'] = chr(188);
    $chars['&frac34;'] = chr(190);
    $chars['&sup2;'] = chr(178);
    $chars['&sup3;'] = chr(179);
    $chars['&times;'] = chr(215);
    $chars['&micro;'] = chr(181);
    $chars['&plusmn;'] = chr(177);

    foreach ($chars as $find => $replace) {
        $string = str_replace($find, $replace, $string);
    }

    return $string;
}

function NX_CreateQTI2Tree()
{
    global $tree;

    $tree = array();

    // block.ElementGroup
    $block = array('pre', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'address', 'dl', 'ol', 'hr', 'blockquote', 'ul', 'table', 'div');

    // inline.ElementGroup
    $inline = array('hottext', 'img', 'br', 'gap', 'em', 'a', 'code', 'span', 'sub', 'acronym', 'big', 'tt', 'kbd', 'q', 'i', 'dfn', 'abbr', 'strong', 'sup', 'var', 'small', 'samp', 'b', 'cite');

    // inlineStatic.ElementGroup
    $inlineStatic = array('hottext', 'img', 'br', 'gap', 'em', 'a', 'code', 'span', 'sub', 'acronym', 'big', 'tt', 'kbd', 'q', 'i', 'dfn', 'abbr', 'strong', 'sup', 'var', 'small', 'samp', 'b', 'cite');

    // flow.ElementGroup
    $flow = array('pre', 'h2', 'h3', 'h1', 'h6', 'h4', 'h5', 'p', 'address', 'dl', 'ol', 'img', 'br', 'ul', 'hr', 'blockquote', 'hottext', 'em', 'a', 'code', 'span', 'sub', 'acronym', 'big', 'tt', 'kbd', 'q', 'i', 'dfn', 'abbr', 'strong', 'sup', 'var', 'small', 'samp', 'b', 'cite', 'table', 'div');

    // bodyElement.AttrGroup
    $bea = array('id', 'class', 'label');

    $tree['itemBody'] = array(
    'tags' => $block,
    'attr' => array()
    );

    $tree['prompt'] = array(
    'tags' => $inlineStatic,
    'attr' => array()
    );

    $tree['feedbackInline'] = array(
    'tags' => $inline,
    'attr' => array()
    );

    $tree['pre'] = array(
    'tags' => $inline,
    'attr' => $bea
    );

    $tree['h1'] = $tree['pre'];

    $tree['h2'] = $tree['pre'];

    $tree['h3'] = $tree['pre'];

    $tree['h4'] = $tree['pre'];

    $tree['h5'] = $tree['pre'];

    $tree['h6'] = $tree['pre'];

    $tree['p'] = $tree['pre'];

    $tree['dl'] = array(
    'tags' => array('dt', 'dd'),
    'attr' => $bea
    );

    $tree['ol'] = array(
    'tags' => array('li'),
    'attr' => $bea
    );

    $tree['hr'] = array(
    'tags' => array(),
    'attr' => $bea
    );

    $tree['blockquote'] = $tree['itemBody'];

    $tree['ul'] = array(
    'tags' => array('li'),
    'attr' => $bea
    );

    $tree['table'] = array(
    'tags' => array('caption', 'col', 'colgroup', 'thead', 'tfoot', 'tbody'),
    'attr' => $bea
    );

    $tree['div'] = array(
    'tags' => array('dt', 'dd'),
    'attr' => $bea
    );

    $tree['dl'] = array(
    'tags' => array('dt', 'dd'),
    'attr' => $bea
    );

    $tree['dl'] = array(
    'tags' => array('dt', 'dd'),
    'attr' => $bea
    );

    $tree['address'] = $tree['pre'];

    $tree['hottext'] = array(
    'tags' => $inlineStatic,
    'attr' => $bea
    );

    $tree['img'] = array(
    'tags' => array(),
    'attr' => array('id', 'class', 'label', 'src', 'alt', 'longdesc', 'height', 'width')
    );

    $tree['br'] = array(
    'tags' => array(),
    'attr' => $bea
    );

    $tree['gap'] = array(
    'tags' => array(),
    'attr' => array('id', 'class', 'label', 'identifier', 'fixed', 'templateIdentifier', 'showHide', 'matchGroup', 'required')
    );

    $tree['em'] = $tree['pre'];

    $tree['a'] = array(
    'tags' => $inline,
    'attr' => array('id', 'class', 'label', 'href', 'type')
    );

    $tree['code'] = $tree['pre'];

    $tree['span'] = $tree['pre'];

    $tree['sub'] = $tree['pre'];
    $tree['acronym'] = $tree['pre'];
    $tree['big'] = $tree['pre'];
    $tree['tt'] = $tree['pre'];
    $tree['kbd'] = $tree['pre'];
    $tree['q'] = $tree['pre'];
    $tree['i'] = $tree['pre'];
    $tree['dfn'] = $tree['pre'];
    $tree['abbr'] = $tree['pre'];
    $tree['strong'] = $tree['pre'];
    $tree['sup'] = $tree['pre'];
    $tree['var'] = $tree['pre'];
    $tree['small'] = $tree['pre'];
    $tree['samp'] = $tree['pre'];
    $tree['b'] = $tree['pre'];
    $tree['cite'] = $tree['pre'];

    $tree['dt'] = array(
    'tags' => $inline,
    'attr' => array()
    );

    $tree['dd'] = array(
    'tags' => $flow,
    'attr' => array()
    );

    $tree['li'] = array(
    'tags' => $flow,
    'attr' => array()
    );

    $tree['caption'] = array(
    'tags' => $inline,
    'attr' => array()
    );

    $tree['col'] = array(
    'tags' => array(),
    'attr' => array('id', 'class', 'label', 'span')
    );

    $tree['colgroup'] = array(
    'tags' => array('col'),
    'attr' => array('id', 'class', 'label', 'span')
    );

    $tree['thead'] = array(
    'tags' => array('tr'),
    'attr' => $bea
    );

    $tree['tfoot'] = $tree['thead'];
    $tree['tbody'] = $tree['thead'];

    $tree['tr'] = array(
    'tags' => array('td', 'th'),
    'attr' => $bea
    );

    $tree['td'] = array(
    'tags' => $flow,
    'attr' => array('id', 'class', 'label', 'headers', 'scope', 'abbr', 'axis', 'rowspan', 'colspan')
    );

    $tree['th'] = $tree['td'];
}
