<?php

if (!defined('ABSPATH'))
    die('Restricted Access');
/* * *****************************************************************************
 * TTFontFile class                                                             *
 *                                                                              *
 * This class is based on The ReportLab Open Source PDF library                 *
 * written in Python - http://www.reportlab.com/software/opensource/            *
 * together with ideas from the OpenOffice source code and others.              *
 *                                                                              *
 * Version:  1.04                                                               *
 * Date:     2011-09-18                                                         *
 * Author:   Ian Back <ianb@bpm1.com>                                           *
 * License:  LGPL                                                               *
 * Copyright (c) Ian Back, 2010                                                 *
 * This header must be retained in any redistribution or                        *
 * modification of the file.                                                    *
 *                                                                              *
 * ***************************************************************************** */

// Define the value used in the "head" table of a created TTF file
// 0x74727565 "true" for Mac
// 0x00010000 for Windows
// Either seems to work for a font embedded in a PDF file
// when read by Adobe Reader on a Windows PC(!)
define("_TTF_MAC_HEADER", false);


// TrueType Font Glyph operators
define("GF_WORDS", (1 << 0));
define("GF_SCALE", (1 << 3));
define("GF_MORE", (1 << 5));
define("GF_XYSCALE", (1 << 6));
define("GF_TWOBYTWO", (1 << 7));

class TTFontFile {

    var $wpjobportal_maxUni;
    var $_pos;
    var $wpjobportal_numTables;
    var $wpjobportal_searchRange;
    var $wpjobportal_entrySelector;
    var $wpjobportal_rangeShift;
    var $wpjobportal_tables;
    var $otables;
    var $filename;
    var $fh;
    var $hmetrics;
    var $glyphPos;
    var $charToGlyph;
    var $ascent;
    var $wpjobportal_descent;
    var $wpjobportal_name;
    var $familyName;
    var $wpjobportal_styleName;
    var $fullName;
    var $uniqueFontID;
    var $unitsPerEm;
    var $bbox;
    var $wpjobportal_capHeight;
    var $wpjobportal_stemV;
    var $wpjobportal_italicAngle;
    var $flags;
    var $underlinePosition;
    var $underlineThickness;
    var $charWidths;
    var $wpjobportal_defaultWidth;
    var $wpjobportal_maxStrLenRead;

    function __construct() {
        $this->maxStrLenRead = 200000; // Maximum size of glyf table to read in as string (otherwise reads each glyph from file)
    }

    function getMetrics($file) {
        $this->filename = $file;
        $this->fh = fopen($file, 'rb') or die('Can\'t open file ' . esc_url($file));
        $this->_pos = 0;
        $this->charWidths = '';
        $this->glyphPos = array();
        $this->charToGlyph = array();
        $this->tables = array();
        $this->otables = array();
        $this->ascent = 0;
        $this->descent = 0;
        $this->TTCFonts = array();
        $this->version = $wpjobportal_version = $this->read_ulong();
        if ($wpjobportal_version == 0x4F54544F)
            die("Postscript outlines are not supported");
        if ($wpjobportal_version == 0x74746366)
            die("ERROR - TrueType Fonts Collections not supported");
        if (!in_array($wpjobportal_version, array(0x00010000, 0x74727565)))
            die("Not a TrueType font: version=" . esc_html($wpjobportal_version));
        $this->readTableDirectory();
        $this->extractInfo();
        fclose($this->fh);
    }

    function readTableDirectory() {
        $this->numTables = $this->read_ushort();
        $this->searchRange = $this->read_ushort();
        $this->entrySelector = $this->read_ushort();
        $this->rangeShift = $this->read_ushort();
        $this->tables = array();
        for ($wpjobportal_i = 0; $wpjobportal_i < $this->numTables; $wpjobportal_i++) {
            $record = array();
            $record['tag'] = $this->read_tag();
            $record['checksum'] = array($this->read_ushort(), $this->read_ushort());
            $record['offset'] = $this->read_ulong();
            $record['length'] = $this->read_ulong();
            $this->tables[$record['tag']] = $record;
        }
    }

    function sub32($x, $y) {
        $xlo = $x[1];
        $xhi = $x[0];
        $ylo = $y[1];
        $yhi = $y[0];
        if ($ylo > $xlo) {
            $xlo += 1 << 16;
            $yhi += 1;
        }
        $reslo = $xlo - $ylo;
        if ($yhi > $xhi) {
            $xhi += 1 << 16;
        }
        $reshi = $xhi - $yhi;
        $reshi = $reshi & 0xFFFF;
        return array($reshi, $reslo);
    }

    function calcChecksum($wpjobportal_data) {
        if (wpjobportalphplib::wpJP_strlen($wpjobportal_data) % 4) {
            $wpjobportal_data .= wpjobportalphplib::wpJP_str_repeat("\0", (4 - (wpjobportalphplib::wpJP_strlen($wpjobportal_data) % 4)));
        }
        $hi = 0x0000;
        $lo = 0x0000;
        for ($wpjobportal_i = 0; $wpjobportal_i < wpjobportalphplib::wpJP_strlen($wpjobportal_data); $wpjobportal_i+=4) {
            $hi += (ord($wpjobportal_data[$wpjobportal_i]) << 8) + ord($wpjobportal_data[$wpjobportal_i + 1]);
            $lo += (ord($wpjobportal_data[$wpjobportal_i + 2]) << 8) + ord($wpjobportal_data[$wpjobportal_i + 3]);
            $hi += $lo >> 16;
            $lo = $lo & 0xFFFF;
            $hi = $hi & 0xFFFF;
        }
        return array($hi, $lo);
    }

    function get_table_pos($wpjobportal_tag) {
        $offset = $this->tables[$wpjobportal_tag]['offset'];
        $length = $this->tables[$wpjobportal_tag]['length'];
        return array($offset, $length);
    }

    function seek($pos) {
        $this->_pos = $pos;
        fseek($this->fh, $this->_pos);
    }

    function skip($wpjobportal_delta) {
        $this->_pos = $this->_pos + $wpjobportal_delta;
        fseek($this->fh, $this->_pos);
    }

    function seek_table($wpjobportal_tag, $offset_in_table = 0) {
        $tpos = $this->get_table_pos($wpjobportal_tag);
        $this->_pos = $tpos[0] + $offset_in_table;
        fseek($this->fh, $this->_pos);
        return $this->_pos;
    }

    function read_tag() {
        $this->_pos += 4;
        return fread($this->fh, 4);
    }

    function read_short() {
        $this->_pos += 2;
        $s = fread($this->fh, 2);
        $a = (ord($s[0]) << 8) + ord($s[1]);
        if ($a & (1 << 15)) {
            $a = ($a - (1 << 16));
        }
        return $a;
    }

    function unpack_short($s) {
        $a = (ord($s[0]) << 8) + ord($s[1]);
        if ($a & (1 << 15)) {
            $a = ($a - (1 << 16));
        }
        return $a;
    }

    function read_ushort() {
        $this->_pos += 2;
        $s = fread($this->fh, 2);
        return (ord($s[0]) << 8) + ord($s[1]);
    }

    function read_ulong() {
        $this->_pos += 4;
        $s = fread($this->fh, 4);
        // if large uInt32 as an integer, PHP converts it to -ve
        return (ord($s[0]) * 16777216) + (ord($s[1]) << 16) + (ord($s[2]) << 8) + ord($s[3]); // 	16777216  = 1<<24
    }

    function get_ushort($pos) {
        fseek($this->fh, $pos);
        $s = fread($this->fh, 2);
        return (ord($s[0]) << 8) + ord($s[1]);
    }

    function get_ulong($pos) {
        fseek($this->fh, $pos);
        $s = fread($this->fh, 4);
        // iF large uInt32 as an integer, PHP converts it to -ve
        return (ord($s[0]) * 16777216) + (ord($s[1]) << 16) + (ord($s[2]) << 8) + ord($s[3]); // 	16777216  = 1<<24
    }

    function pack_short($wpjobportal_val) {
        if ($wpjobportal_val < 0) {
            $wpjobportal_val = abs($wpjobportal_val);
            $wpjobportal_val = ~$wpjobportal_val;
            $wpjobportal_val += 1;
        }
        return pack("n", $wpjobportal_val);
    }

    function splice($wpjobportal_stream, $offset, $wpjobportal_value) {
        return wpjobportalphplib::wpJP_substr($wpjobportal_stream, 0, $offset) . $wpjobportal_value . wpjobportalphplib::wpJP_substr($wpjobportal_stream, $offset + wpjobportalphplib::wpJP_strlen($wpjobportal_value));
    }

    function _set_ushort($wpjobportal_stream, $offset, $wpjobportal_value) {
        $wpjobportal_up = pack("n", $wpjobportal_value);
        return $this->splice($wpjobportal_stream, $offset, $wpjobportal_up);
    }

    function _set_short($wpjobportal_stream, $offset, $wpjobportal_val) {
        if ($wpjobportal_val < 0) {
            $wpjobportal_val = abs($wpjobportal_val);
            $wpjobportal_val = ~$wpjobportal_val;
            $wpjobportal_val += 1;
        }
        $wpjobportal_up = pack("n", $wpjobportal_val);
        return $this->splice($wpjobportal_stream, $offset, $wpjobportal_up);
    }

    function get_chunk($pos, $length) {
        fseek($this->fh, $pos);
        if ($length < 1) {
            return '';
        }
        return (fread($this->fh, $length));
    }

    function get_table($wpjobportal_tag) {
        list($pos, $length) = $this->get_table_pos($wpjobportal_tag);
        if ($length == 0) {
            die('Truetype font (' . esc_html($this->filename) . '): error reading table: ' . esc_html($wpjobportal_tag));
        }
        fseek($this->fh, $pos);
        return (fread($this->fh, $length));
    }

    function add($wpjobportal_tag, $wpjobportal_data) {
        if ($wpjobportal_tag == 'head') {
            $wpjobportal_data = $this->splice($wpjobportal_data, 8, "\0\0\0\0");
        }
        $this->otables[$wpjobportal_tag] = $wpjobportal_data;
    }

/////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////

    function extractInfo() {
        ///////////////////////////////////
        // name - Naming table
        ///////////////////////////////////
        $this->sFamilyClass = 0;
        $this->sFamilySubClass = 0;

        $wpjobportal_name_offset = $this->seek_table("name");
        $wpjobportal_format = $this->read_ushort();
        if ($wpjobportal_format != 0)
            die("Unknown name table format " . esc_html($wpjobportal_format));
        $wpjobportal_numRecords = $this->read_ushort();
        $wpjobportal_string_data_offset = $wpjobportal_name_offset + $this->read_ushort();
        $wpjobportal_names = array(1 => '', 2 => '', 3 => '', 4 => '', 6 => '');
        $K = array_keys($wpjobportal_names);
        $wpjobportal_nameCount = count($wpjobportal_names);
        for ($wpjobportal_i = 0; $wpjobportal_i < $wpjobportal_numRecords; $wpjobportal_i++) {
            $platformId = $this->read_ushort();
            $wpjobportal_encodingId = $this->read_ushort();
            $wpjobportal_languageId = $this->read_ushort();
            $wpjobportal_nameId = $this->read_ushort();
            $length = $this->read_ushort();
            $offset = $this->read_ushort();
            if (!in_array($wpjobportal_nameId, $K))
                continue;
            $N = '';
            if ($platformId == 3 && $wpjobportal_encodingId == 1 && $wpjobportal_languageId == 0x409) { // Microsoft, Unicode, US English, PS Name
                $opos = $this->_pos;
                $this->seek($wpjobportal_string_data_offset + $offset);
                if ($length % 2 != 0)
                    die("PostScript name is UTF-16BE string of odd length");
                $length /= 2;
                $N = '';
                while ($length > 0) {
                    $char = $this->read_ushort();
                    $N .= (chr($char));
                    $length -= 1;
                }
                $this->_pos = $opos;
                $this->seek($opos);
            } else if ($platformId == 1 && $wpjobportal_encodingId == 0 && $wpjobportal_languageId == 0) { // Macintosh, Roman, English, PS Name
                $opos = $this->_pos;
                $N = $this->get_chunk($wpjobportal_string_data_offset + $offset, $length);
                $this->_pos = $opos;
                $this->seek($opos);
            }
            if ($N && $wpjobportal_names[$wpjobportal_nameId] == '') {
                $wpjobportal_names[$wpjobportal_nameId] = $N;
                $wpjobportal_nameCount -= 1;
                if ($wpjobportal_nameCount == 0)
                    break;
            }
        }
        if ($wpjobportal_names[6])
            $psName = $wpjobportal_names[6];
        else if ($wpjobportal_names[4])
            $psName = wpjobportalphplib::wpJP_preg_replace('/ /', '-', $wpjobportal_names[4]);
        else if ($wpjobportal_names[1])
            $psName = wpjobportalphplib::wpJP_preg_replace('/ /', '-', $wpjobportal_names[1]);
        else
            $psName = '';
        if (!$psName)
            die("Could not find PostScript font name");
        $this->name = $psName;
        if ($wpjobportal_names[1]) {
            $this->familyName = $wpjobportal_names[1];
        } else {
            $this->familyName = $psName;
        }
        if ($wpjobportal_names[2]) {
            $this->styleName = $wpjobportal_names[2];
        } else {
            $this->styleName = 'Regular';
        }
        if ($wpjobportal_names[4]) {
            $this->fullName = $wpjobportal_names[4];
        } else {
            $this->fullName = $psName;
        }
        if ($wpjobportal_names[3]) {
            $this->uniqueFontID = $wpjobportal_names[3];
        } else {
            $this->uniqueFontID = $psName;
        }
        if ($wpjobportal_names[6]) {
            $this->fullName = $wpjobportal_names[6];
        }

        ///////////////////////////////////
        // head - Font header table
        ///////////////////////////////////
        $this->seek_table("head");
        $this->skip(18);
        $this->unitsPerEm = $unitsPerEm = $this->read_ushort();
        $scale = 1000 / $unitsPerEm;
        $this->skip(16);
        $xMin = $this->read_short();
        $yMin = $this->read_short();
        $xMax = $this->read_short();
        $yMax = $this->read_short();
        $this->bbox = array(($xMin * $scale), ($yMin * $scale), ($xMax * $scale), ($yMax * $scale));
        $this->skip(3 * 2);
        $wpjobportal_indexToLocFormat = $this->read_ushort();
        $glyphDataFormat = $this->read_ushort();
        if ($glyphDataFormat != 0)
            die('Unknown glyph data format ' . esc_html($glyphDataFormat));

        ///////////////////////////////////
        // hhea metrics table
        ///////////////////////////////////
        // ttf2t1 seems to use this value rather than the one in OS/2 - so put in for compatibility
        if (isset($this->tables["hhea"])) {
            $this->seek_table("hhea");
            $this->skip(4);
            $hheaAscender = $this->read_short();
            $hheaDescender = $this->read_short();
            $this->ascent = ($hheaAscender * $scale);
            $this->descent = ($hheaDescender * $scale);
        }

        ///////////////////////////////////
        // OS/2 - OS/2 and Windows metrics table
        ///////////////////////////////////
        if (isset($this->tables["OS/2"])) {
            $this->seek_table("OS/2");
            $wpjobportal_version = $this->read_ushort();
            $this->skip(2);
            $usWeightClass = $this->read_ushort();
            $this->skip(2);
            $fsType = $this->read_ushort();
            if ($fsType == 0x0002 || ($fsType & 0x0300) != 0) {
                die('ERROR - Font file ' . esc_html($this->filename) . ' cannot be embedded due to copyright restrictions.');
                $this->restrictedUse = true;
            }
            $this->skip(20);
            $sF = $this->read_short();
            $this->sFamilyClass = ($sF >> 8);
            $this->sFamilySubClass = ($sF & 0xFF);
            $this->_pos += 10;  //PANOSE = 10 byte length
            $panose = fread($this->fh, 10);
            $this->skip(26);
            $sTypoAscender = $this->read_short();
            $sTypoDescender = $this->read_short();
            if (!$this->ascent)
                $this->ascent = ($sTypoAscender * $scale);
            if (!$this->descent)
                $this->descent = ($sTypoDescender * $scale);
            if ($wpjobportal_version > 1) {
                $this->skip(16);
                $sCapHeight = $this->read_short();
                $this->capHeight = ($sCapHeight * $scale);
            } else {
                $this->capHeight = $this->ascent;
            }
        } else {
            $usWeightClass = 500;
            if (!$this->ascent)
                $this->ascent = ($yMax * $scale);
            if (!$this->descent)
                $this->descent = ($yMin * $scale);
            $this->capHeight = $this->ascent;
        }
        $this->stemV = 50 + intval(pow(($usWeightClass / 65.0), 2));

        ///////////////////////////////////
        // post - PostScript table
        ///////////////////////////////////
        $this->seek_table("post");
        $this->skip(4);
        $this->italicAngle = $this->read_short() + $this->read_ushort() / 65536.0;
        $this->underlinePosition = $this->read_short() * $scale;
        $this->underlineThickness = $this->read_short() * $scale;
        $wpjobportal_isFixedPitch = $this->read_ulong();

        $this->flags = 4;

        if ($this->italicAngle != 0)
            $this->flags = $this->flags | 64;
        if ($usWeightClass >= 600)
            $this->flags = $this->flags | 262144;
        if ($wpjobportal_isFixedPitch)
            $this->flags = $this->flags | 1;

        ///////////////////////////////////
        // hhea - Horizontal header table
        ///////////////////////////////////
        $this->seek_table("hhea");
        $this->skip(32);
        $metricDataFormat = $this->read_ushort();
        if ($metricDataFormat != 0)
            die('Unknown horizontal metric data format ' . esc_html($metricDataFormat));
        $wpjobportal_numberOfHMetrics = $this->read_ushort();
        if ($wpjobportal_numberOfHMetrics == 0)
            die('Number of horizontal metrics is 0');

        ///////////////////////////////////
        // maxp - Maximum profile table
        ///////////////////////////////////
        $this->seek_table("maxp");
        $this->skip(4);
        $wpjobportal_numGlyphs = $this->read_ushort();


        ///////////////////////////////////
        // cmap - Character to glyph index mapping table
        ///////////////////////////////////
        $cmap_offset = $this->seek_table("cmap");
        $this->skip(2);
        $cmapTableCount = $this->read_ushort();
        $unicode_cmap_offset = 0;
        for ($wpjobportal_i = 0; $wpjobportal_i < $cmapTableCount; $wpjobportal_i++) {
            $platformID = $this->read_ushort();
            $wpjobportal_encodingID = $this->read_ushort();
            $offset = $this->read_ulong();
            $wpjobportal_save_pos = $this->_pos;
            if (($platformID == 3 && $wpjobportal_encodingID == 1) || $platformID == 0) { // Microsoft, Unicode
                $wpjobportal_format = $this->get_ushort($cmap_offset + $offset);
                if ($wpjobportal_format == 4) {
                    if (!$unicode_cmap_offset)
                        $unicode_cmap_offset = $cmap_offset + $offset;
                    break;
                }
            }
            $this->seek($wpjobportal_save_pos);
        }
        if (!$unicode_cmap_offset)
            die('Font (' . esc_html($this->filename) . ') does not have cmap for Unicode (platform 3, encoding 1, format 4, or platform 0, any encoding, format 4)');


        $glyphToChar = array();
        $charToGlyph = array();
        $this->getCMAP4($unicode_cmap_offset, $glyphToChar, $charToGlyph);

        ///////////////////////////////////
        // hmtx - Horizontal metrics table
        ///////////////////////////////////
        $this->getHMTX($wpjobportal_numberOfHMetrics, $wpjobportal_numGlyphs, $glyphToChar, $scale);
    }

/////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////


    function makeSubset($file, &$wpjobportal_subset) {
        $this->filename = $file;
        $this->fh = fopen($file, 'rb') or die('Can\'t open file ' . esc_url($file));
        $this->_pos = 0;
        $this->charWidths = '';
        $this->glyphPos = array();
        $this->charToGlyph = array();
        $this->tables = array();
        $this->otables = array();
        $this->ascent = 0;
        $this->descent = 0;
        $this->skip(4);
        $this->maxUni = 0;
        $this->readTableDirectory();


        ///////////////////////////////////
        // head - Font header table
        ///////////////////////////////////
        $this->seek_table("head");
        $this->skip(50);
        $wpjobportal_indexToLocFormat = $this->read_ushort();
        $glyphDataFormat = $this->read_ushort();

        ///////////////////////////////////
        // hhea - Horizontal header table
        ///////////////////////////////////
        $this->seek_table("hhea");
        $this->skip(32);
        $metricDataFormat = $this->read_ushort();
        $wpjobportal_orignHmetrics = $wpjobportal_numberOfHMetrics = $this->read_ushort();

        ///////////////////////////////////
        // maxp - Maximum profile table
        ///////////////////////////////////
        $this->seek_table("maxp");
        $this->skip(4);
        $wpjobportal_numGlyphs = $this->read_ushort();


        ///////////////////////////////////
        // cmap - Character to glyph index mapping table
        ///////////////////////////////////
        $cmap_offset = $this->seek_table("cmap");
        $this->skip(2);
        $cmapTableCount = $this->read_ushort();
        $unicode_cmap_offset = 0;
        for ($wpjobportal_i = 0; $wpjobportal_i < $cmapTableCount; $wpjobportal_i++) {
            $platformID = $this->read_ushort();
            $wpjobportal_encodingID = $this->read_ushort();
            $offset = $this->read_ulong();
            $wpjobportal_save_pos = $this->_pos;
            if (($platformID == 3 && $wpjobportal_encodingID == 1) || $platformID == 0) { // Microsoft, Unicode
                $wpjobportal_format = $this->get_ushort($cmap_offset + $offset);
                if ($wpjobportal_format == 4) {
                    $unicode_cmap_offset = $cmap_offset + $offset;
                    break;
                }
            }
            $this->seek($wpjobportal_save_pos);
        }

        if (!$unicode_cmap_offset)
            die('Font (' . esc_html($this->filename) . ') does not have cmap for Unicode (platform 3, encoding 1, format 4, or platform 0, any encoding, format 4)');


        $glyphToChar = array();
        $charToGlyph = array();
        $this->getCMAP4($unicode_cmap_offset, $glyphToChar, $charToGlyph);

        $this->charToGlyph = $charToGlyph;

        ///////////////////////////////////
        // hmtx - Horizontal metrics table
        ///////////////////////////////////
        $scale = 1; // not used
        $this->getHMTX($wpjobportal_numberOfHMetrics, $wpjobportal_numGlyphs, $glyphToChar, $scale);

        ///////////////////////////////////
        // loca - Index to location
        ///////////////////////////////////
        $this->getLOCA($wpjobportal_indexToLocFormat, $wpjobportal_numGlyphs);

        $wpjobportal_subsetglyphs = array(0 => 0);
        $wpjobportal_subsetCharToGlyph = array();
        foreach ($wpjobportal_subset AS $code) {
            if (isset($this->charToGlyph[$code])) {
                $wpjobportal_subsetglyphs[$this->charToGlyph[$code]] = $code; // Old Glyph ID => Unicode
                $wpjobportal_subsetCharToGlyph[$code] = $this->charToGlyph[$code]; // Unicode to old GlyphID
            }
            $this->maxUni = max($this->maxUni, $code);
        }

        list($wpjobportal_start, $dummy) = $this->get_table_pos('glyf');

        $glyphSet = array();
        ksort($wpjobportal_subsetglyphs);
        $wpjobportal_n = 0;
        $fsLastCharIndex = 0; // maximum Unicode index (character code) in this font, according to the cmap subtable for platform ID 3 and platform- specific encoding ID 0 or 1.
        foreach ($wpjobportal_subsetglyphs AS $wpjobportal_originalGlyphIdx => $uni) {
            $fsLastCharIndex = max($fsLastCharIndex, $uni);
            $glyphSet[$wpjobportal_originalGlyphIdx] = $wpjobportal_n; // old glyphID to new glyphID
            $wpjobportal_n++;
        }

        ksort($wpjobportal_subsetCharToGlyph);
        foreach ($wpjobportal_subsetCharToGlyph AS $uni => $wpjobportal_originalGlyphIdx) {
            $codeToGlyph[$uni] = $glyphSet[$wpjobportal_originalGlyphIdx];
        }
        $this->codeToGlyph = $codeToGlyph;

        ksort($wpjobportal_subsetglyphs);
        foreach ($wpjobportal_subsetglyphs AS $wpjobportal_originalGlyphIdx => $uni) {
            $this->getGlyphs($wpjobportal_originalGlyphIdx, $wpjobportal_start, $glyphSet, $wpjobportal_subsetglyphs);
        }

        $wpjobportal_numGlyphs = $wpjobportal_numberOfHMetrics = count($wpjobportal_subsetglyphs);

        //tables copied from the original
        $wpjobportal_tags = array('name');
        foreach ($wpjobportal_tags AS $wpjobportal_tag) {
            $this->add($wpjobportal_tag, $this->get_table($wpjobportal_tag));
        }
        $wpjobportal_tags = array('cvt ', 'fpgm', 'prep', 'gasp');
        foreach ($wpjobportal_tags AS $wpjobportal_tag) {
            if (isset($this->tables[$wpjobportal_tag])) {
                $this->add($wpjobportal_tag, $this->get_table($wpjobportal_tag));
            }
        }

        // post - PostScript
        $opost = $this->get_table('post');
        $post = "\x00\x03\x00\x00" . wpjobportalphplib::wpJP_substr($opost, 4, 12) . "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00";
        $this->add('post', $post);

        // Sort CID2GID map into segments of contiguous codes
        ksort($codeToGlyph);
        unset($codeToGlyph[0]);
        //unset($codeToGlyph[65535]);
        $wpjobportal_rangeid = 0;
        $wpjobportal_range = array();
        $wpjobportal_prevcid = -2;
        $wpjobportal_prevglidx = -1;
        // for each character
        foreach ($codeToGlyph as $cid => $glidx) {
            if ($cid == ($wpjobportal_prevcid + 1) && $glidx == ($wpjobportal_prevglidx + 1)) {
                $wpjobportal_range[$wpjobportal_rangeid][] = $glidx;
            } else {
                // new range
                $wpjobportal_rangeid = $cid;
                $wpjobportal_range[$wpjobportal_rangeid] = array();
                $wpjobportal_range[$wpjobportal_rangeid][] = $glidx;
            }
            $wpjobportal_prevcid = $cid;
            $wpjobportal_prevglidx = $glidx;
        }

        // cmap - Character to glyph mapping - Format 4 (MS / )
        $wpjobportal_segCount = count($wpjobportal_range) + 1; // + 1 Last segment has missing character 0xFFFF
        $wpjobportal_searchRange = 1;
        $wpjobportal_entrySelector = 0;
        while ($wpjobportal_searchRange * 2 <= $wpjobportal_segCount) {
            $wpjobportal_searchRange = $wpjobportal_searchRange * 2;
            $wpjobportal_entrySelector = $wpjobportal_entrySelector + 1;
        }
        $wpjobportal_searchRange = $wpjobportal_searchRange * 2;
        $wpjobportal_rangeShift = $wpjobportal_segCount * 2 - $wpjobportal_searchRange;
        $length = 16 + (8 * $wpjobportal_segCount ) + ($wpjobportal_numGlyphs + 1);
        $cmap = array(0, 1, // Index : version, number of encoding subtables
            3, 1, // Encoding Subtable : platform (MS=3), encoding (Unicode)
            0, 12, // Encoding Subtable : offset (hi,lo)
            4, $length, 0, // Format 4 Mapping subtable: format, length, language
            $wpjobportal_segCount * 2,
            $wpjobportal_searchRange,
            $wpjobportal_entrySelector,
            $wpjobportal_rangeShift);

        // endCode(s)
        foreach ($wpjobportal_range AS $wpjobportal_start => $wpjobportal_subrange) {
            $wpjobportal_endCode = $wpjobportal_start + (count($wpjobportal_subrange) - 1);
            $cmap[] = $wpjobportal_endCode; // endCode(s)
        }
        $cmap[] = 0xFFFF; // endCode of last Segment
        $cmap[] = 0; // reservedPad
        // startCode(s)
        foreach ($wpjobportal_range AS $wpjobportal_start => $wpjobportal_subrange) {
            $cmap[] = $wpjobportal_start; // startCode(s)
        }
        $cmap[] = 0xFFFF; // startCode of last Segment
        // idDelta(s) 
        foreach ($wpjobportal_range AS $wpjobportal_start => $wpjobportal_subrange) {
            $wpjobportal_idDelta = -($wpjobportal_start - $wpjobportal_subrange[0]);
            $wpjobportal_n += count($wpjobportal_subrange);
            $cmap[] = $wpjobportal_idDelta; // idDelta(s)
        }
        $cmap[] = 1; // idDelta of last Segment
        // idRangeOffset(s) 
        foreach ($wpjobportal_range AS $wpjobportal_subrange) {
            $cmap[] = 0; // idRangeOffset[segCount]  	Offset in bytes to glyph indexArray, or 0
        }
        $cmap[] = 0; // idRangeOffset of last Segment
        foreach ($wpjobportal_range AS $wpjobportal_subrange) {
            foreach ($wpjobportal_subrange AS $glidx) {
                $cmap[] = $glidx;
            }
        }
        $cmap[] = 0; // Mapping for last character
        $cmapstr = '';
        foreach ($cmap AS $cm) {
            $cmapstr .= pack("n", $cm);
        }
        $this->add('cmap', $cmapstr);


        // glyf - Glyph data
        list($glyfOffset, $glyfLength) = $this->get_table_pos('glyf');
        if ($glyfLength < $this->maxStrLenRead) {
            $glyphData = $this->get_table('glyf');
        }

        $offsets = array();
        $glyf = '';
        $pos = 0;

        $hmtxstr = '';
        $xMinT = 0;
        $yMinT = 0;
        $xMaxT = 0;
        $yMaxT = 0;
        $advanceWidthMax = 0;
        $minLeftSideBearing = 0;
        $minRightSideBearing = 0;
        $xMaxExtent = 0;
        $wpjobportal_maxPoints = 0;   // points in non-compound glyph
        $wpjobportal_maxContours = 0;   // contours in non-compound glyph
        $wpjobportal_maxComponentPoints = 0; // points in compound glyph
        $wpjobportal_maxComponentContours = 0; // contours in compound glyph
        $wpjobportal_maxComponentElements = 0; // number of glyphs referenced at top level
        $wpjobportal_maxComponentDepth = 0;  // levels of recursion, set to 0 if font has only simple glyphs
        $this->glyphdata = array();

        foreach ($wpjobportal_subsetglyphs AS $wpjobportal_originalGlyphIdx => $uni) {
            // hmtx - Horizontal Metrics
            $hm = $this->getHMetric($wpjobportal_orignHmetrics, $wpjobportal_originalGlyphIdx);
            $hmtxstr .= $hm;

            $offsets[] = $pos;
            $glyphPos = $this->glyphPos[$wpjobportal_originalGlyphIdx];
            $glyphLen = $this->glyphPos[$wpjobportal_originalGlyphIdx + 1] - $glyphPos;
            if ($glyfLength < $this->maxStrLenRead) {
                $wpjobportal_data = wpjobportalphplib::wpJP_substr($glyphData, $glyphPos, $glyphLen);
            } else {
                if ($glyphLen > 0)
                    $wpjobportal_data = $this->get_chunk($glyfOffset + $glyphPos, $glyphLen);
                else
                    $wpjobportal_data = '';
            }

            if ($glyphLen > 0) {
                $wpjobportal_up = unpack("n", wpjobportalphplib::wpJP_substr($wpjobportal_data, 0, 2));
            }

            if ($glyphLen > 2 && ($wpjobportal_up[1] & (1 << 15))) { // If number of contours <= -1 i.e. composiste glyph
                $pos_in_glyph = 10;
                $flags = GF_MORE;
                $wpjobportal_nComponentElements = 0;
                while ($flags & GF_MORE) {
                    $wpjobportal_nComponentElements += 1; // number of glyphs referenced at top level
                    $wpjobportal_up = unpack("n", wpjobportalphplib::wpJP_substr($wpjobportal_data, $pos_in_glyph, 2));
                    $flags = $wpjobportal_up[1];
                    $wpjobportal_up = unpack("n", wpjobportalphplib::wpJP_substr($wpjobportal_data, $pos_in_glyph + 2, 2));
                    $glyphIdx = $wpjobportal_up[1];
                    $this->glyphdata[$wpjobportal_originalGlyphIdx]['compGlyphs'][] = $glyphIdx;
                    $wpjobportal_data = $this->_set_ushort($wpjobportal_data, $pos_in_glyph + 2, $glyphSet[$glyphIdx]);
                    $pos_in_glyph += 4;
                    if ($flags & GF_WORDS) {
                        $pos_in_glyph += 4;
                    } else {
                        $pos_in_glyph += 2;
                    }
                    if ($flags & GF_SCALE) {
                        $pos_in_glyph += 2;
                    } else if ($flags & GF_XYSCALE) {
                        $pos_in_glyph += 4;
                    } else if ($flags & GF_TWOBYTWO) {
                        $pos_in_glyph += 8;
                    }
                }
                $wpjobportal_maxComponentElements = max($wpjobportal_maxComponentElements, $wpjobportal_nComponentElements);
            }

            $glyf .= $wpjobportal_data;
            $pos += $glyphLen;
            if ($pos % 4 != 0) {
                $wpjobportal_padding = 4 - ($pos % 4);
                $glyf .= wpjobportalphplib::wpJP_str_repeat("\0", $wpjobportal_padding);
                $pos += $wpjobportal_padding;
            }
        }

        $offsets[] = $pos;
        $this->add('glyf', $glyf);

        // hmtx - Horizontal Metrics
        $this->add('hmtx', $hmtxstr);

        // loca - Index to location
        $locastr = '';
        if ((($pos + 1) >> 1) > 0xFFFF) {
            $wpjobportal_indexToLocFormat = 1;        // long format
            foreach ($offsets AS $offset) {
                $locastr .= pack("N", $offset);
            }
        } else {
            $wpjobportal_indexToLocFormat = 0;        // short format
            foreach ($offsets AS $offset) {
                $locastr .= pack("n", ($offset / 2));
            }
        }
        $this->add('loca', $locastr);

        // head - Font header
        $head = $this->get_table('head');
        $head = $this->_set_ushort($head, 50, $wpjobportal_indexToLocFormat);
        $this->add('head', $head);


        // hhea - Horizontal Header
        $hhea = $this->get_table('hhea');
        $hhea = $this->_set_ushort($hhea, 34, $wpjobportal_numberOfHMetrics);
        $this->add('hhea', $hhea);

        // maxp - Maximum Profile
        $wpjobportal_maxp = $this->get_table('maxp');
        $wpjobportal_maxp = $this->_set_ushort($wpjobportal_maxp, 4, $wpjobportal_numGlyphs);
        $this->add('maxp', $wpjobportal_maxp);


        // OS/2 - OS/2
        $os2 = $this->get_table('OS/2');
        $this->add('OS/2', $os2);

        fclose($this->fh);

        // Put the TTF file together
        $wpjobportal_stm = '';
        $this->endTTFile($wpjobportal_stm);
        return $wpjobportal_stm;
    }

    //////////////////////////////////////////////////////////////////////////////////
    // Recursively get composite glyph data
    function getGlyphData($wpjobportal_originalGlyphIdx, &$wpjobportal_maxdepth, &$wpjobportal_depth, &$points, &$contours) {
        $wpjobportal_depth++;
        $wpjobportal_maxdepth = max($wpjobportal_maxdepth, $wpjobportal_depth);
        if (count($this->glyphdata[$wpjobportal_originalGlyphIdx]['compGlyphs'])) {
            foreach ($this->glyphdata[$wpjobportal_originalGlyphIdx]['compGlyphs'] AS $glyphIdx) {
                $this->getGlyphData($glyphIdx, $wpjobportal_maxdepth, $wpjobportal_depth, $points, $contours);
            }
        } else if (($this->glyphdata[$wpjobportal_originalGlyphIdx]['nContours'] > 0) && $wpjobportal_depth > 0) { // simple
            $contours += $this->glyphdata[$wpjobportal_originalGlyphIdx]['nContours'];
            $points += $this->glyphdata[$wpjobportal_originalGlyphIdx]['nPoints'];
        }
        $wpjobportal_depth--;
    }

    //////////////////////////////////////////////////////////////////////////////////
    // Recursively get composite glyphs
    function getGlyphs($wpjobportal_originalGlyphIdx, &$wpjobportal_start, &$glyphSet, &$wpjobportal_subsetglyphs) {
        $glyphPos = $this->glyphPos[$wpjobportal_originalGlyphIdx];
        $glyphLen = $this->glyphPos[$wpjobportal_originalGlyphIdx + 1] - $glyphPos;
        if (!$glyphLen) {
            return;
        }
        $this->seek($wpjobportal_start + $glyphPos);
        $wpjobportal_numberOfContours = $this->read_short();
        if ($wpjobportal_numberOfContours < 0) {
            $this->skip(8);
            $flags = GF_MORE;
            while ($flags & GF_MORE) {
                $flags = $this->read_ushort();
                $glyphIdx = $this->read_ushort();
                if (!isset($glyphSet[$glyphIdx])) {
                    $glyphSet[$glyphIdx] = count($wpjobportal_subsetglyphs); // old glyphID to new glyphID
                    $wpjobportal_subsetglyphs[$glyphIdx] = true;
                }
                $wpjobportal_savepos = ftell($this->fh);
                $this->getGlyphs($glyphIdx, $wpjobportal_start, $glyphSet, $wpjobportal_subsetglyphs);
                $this->seek($wpjobportal_savepos);
                if ($flags & GF_WORDS)
                    $this->skip(4);
                else
                    $this->skip(2);
                if ($flags & GF_SCALE)
                    $this->skip(2);
                else if ($flags & GF_XYSCALE)
                    $this->skip(4);
                else if ($flags & GF_TWOBYTWO)
                    $this->skip(8);
            }
        }
    }

    //////////////////////////////////////////////////////////////////////////////////

    function getHMTX($wpjobportal_numberOfHMetrics, $wpjobportal_numGlyphs, &$glyphToChar, $scale) {
        $wpjobportal_start = $this->seek_table("hmtx");
        $aw = 0;
        $this->charWidths = str_pad('', 256 * 256 * 2, "\x00");
        $wpjobportal_nCharWidths = 0;
        if (($wpjobportal_numberOfHMetrics * 4) < $this->maxStrLenRead) {
            $wpjobportal_data = $this->get_chunk($wpjobportal_start, ($wpjobportal_numberOfHMetrics * 4));
            $wpjobportal_arr = unpack("n*", $wpjobportal_data);
        } else {
            $this->seek($wpjobportal_start);
        }
        for ($glyph = 0; $glyph < $wpjobportal_numberOfHMetrics; $glyph++) {

            if (($wpjobportal_numberOfHMetrics * 4) < $this->maxStrLenRead) {
                $aw = $wpjobportal_arr[($glyph * 2) + 1];
            } else {
                $aw = $this->read_ushort();
                $lsb = $this->read_ushort();
            }
            if (isset($glyphToChar[$glyph]) || $glyph == 0) {

                if ($aw >= (1 << 15)) {
                    $aw = 0;
                } // 1.03 Some (arabic) fonts have -ve values for width
                // although should be unsigned value - comes out as e.g. 65108 (intended -50)
                if ($glyph == 0) {
                    $this->defaultWidth = $scale * $aw;
                    continue;
                }
                foreach ($glyphToChar[$glyph] AS $char) {
                    if ($char != 0 && $char != 65535) {
                        $w = intval(round($scale * $aw));
                        if ($w == 0) {
                            $w = 65535;
                        }
                        if ($char < 196608) {
                            $this->charWidths[$char * 2] = chr($w >> 8);
                            $this->charWidths[$char * 2 + 1] = chr($w & 0xFF);
                            $wpjobportal_nCharWidths++;
                        }
                    }
                }
            }
        }
        $wpjobportal_data = $this->get_chunk(($wpjobportal_start + $wpjobportal_numberOfHMetrics * 4), ($wpjobportal_numGlyphs * 2));
        $wpjobportal_arr = unpack("n*", $wpjobportal_data);
        $wpjobportal_diff = $wpjobportal_numGlyphs - $wpjobportal_numberOfHMetrics;
        for ($pos = 0; $pos < $wpjobportal_diff; $pos++) {
            $glyph = $pos + $wpjobportal_numberOfHMetrics;
            if (isset($glyphToChar[$glyph])) {
                foreach ($glyphToChar[$glyph] AS $char) {
                    if ($char != 0 && $char != 65535) {
                        $w = intval(round($scale * $aw));
                        if ($w == 0) {
                            $w = 65535;
                        }
                        if ($char < 196608) {
                            $this->charWidths[$char * 2] = chr($w >> 8);
                            $this->charWidths[$char * 2 + 1] = chr($w & 0xFF);
                            $wpjobportal_nCharWidths++;
                        }
                    }
                }
            }
        }
        // NB 65535 is a set width of 0
        // First bytes define number of chars in font
        $this->charWidths[0] = chr($wpjobportal_nCharWidths >> 8);
        $this->charWidths[1] = chr($wpjobportal_nCharWidths & 0xFF);
    }

    function getHMetric($wpjobportal_numberOfHMetrics, $gid) {
        $wpjobportal_start = $this->seek_table("hmtx");
        if ($gid < $wpjobportal_numberOfHMetrics) {
            $this->seek($wpjobportal_start + ($gid * 4));
            $hm = fread($this->fh, 4);
        } else {
            $this->seek($wpjobportal_start + (($wpjobportal_numberOfHMetrics - 1) * 4));
            $hm = fread($this->fh, 2);
            $this->seek($wpjobportal_start + ($wpjobportal_numberOfHMetrics * 2) + ($gid * 2));
            $hm .= fread($this->fh, 2);
        }
        return $hm;
    }

    function getLOCA($wpjobportal_indexToLocFormat, $wpjobportal_numGlyphs) {
        $wpjobportal_start = $this->seek_table('loca');
        $this->glyphPos = array();
        if ($wpjobportal_indexToLocFormat == 0) {
            $wpjobportal_data = $this->get_chunk($wpjobportal_start, ($wpjobportal_numGlyphs * 2) + 2);
            $wpjobportal_arr = unpack("n*", $wpjobportal_data);
            for ($wpjobportal_n = 0; $wpjobportal_n <= $wpjobportal_numGlyphs; $wpjobportal_n++) {
                $this->glyphPos[] = ($wpjobportal_arr[$wpjobportal_n + 1] * 2);
            }
        } else if ($wpjobportal_indexToLocFormat == 1) {
            $wpjobportal_data = $this->get_chunk($wpjobportal_start, ($wpjobportal_numGlyphs * 4) + 4);
            $wpjobportal_arr = unpack("N*", $wpjobportal_data);
            for ($wpjobportal_n = 0; $wpjobportal_n <= $wpjobportal_numGlyphs; $wpjobportal_n++) {
                $this->glyphPos[] = ($wpjobportal_arr[$wpjobportal_n + 1]);
            }
        } else
            die('Unknown location table format ' . esc_html($wpjobportal_indexToLocFormat));
    }

    // CMAP Format 4
    function getCMAP4($unicode_cmap_offset, &$glyphToChar, &$charToGlyph) {
        $this->maxUniChar = 0;
        $this->seek($unicode_cmap_offset + 2);
        $length = $this->read_ushort();
        $limit = $unicode_cmap_offset + $length;
        $this->skip(2);

        $wpjobportal_segCount = $this->read_ushort() / 2;
        $this->skip(6);
        $wpjobportal_endCount = array();
        for ($wpjobportal_i = 0; $wpjobportal_i < $wpjobportal_segCount; $wpjobportal_i++) {
            $wpjobportal_endCount[] = $this->read_ushort();
        }
        $this->skip(2);
        $wpjobportal_startCount = array();
        for ($wpjobportal_i = 0; $wpjobportal_i < $wpjobportal_segCount; $wpjobportal_i++) {
            $wpjobportal_startCount[] = $this->read_ushort();
        }
        $wpjobportal_idDelta = array();
        for ($wpjobportal_i = 0; $wpjobportal_i < $wpjobportal_segCount; $wpjobportal_i++) {
            $wpjobportal_idDelta[] = $this->read_short();
        }  // ???? was unsigned short
        $wpjobportal_idRangeOffset_start = $this->_pos;
        $wpjobportal_idRangeOffset = array();
        for ($wpjobportal_i = 0; $wpjobportal_i < $wpjobportal_segCount; $wpjobportal_i++) {
            $wpjobportal_idRangeOffset[] = $this->read_ushort();
        }

        for ($wpjobportal_n = 0; $wpjobportal_n < $wpjobportal_segCount; $wpjobportal_n++) {
            $wpjobportal_endpoint = ($wpjobportal_endCount[$wpjobportal_n] + 1);
            for ($unichar = $wpjobportal_startCount[$wpjobportal_n]; $unichar < $wpjobportal_endpoint; $unichar++) {
                if ($wpjobportal_idRangeOffset[$wpjobportal_n] == 0)
                    $glyph = ($unichar + $wpjobportal_idDelta[$wpjobportal_n]) & 0xFFFF;
                else {
                    $offset = ($unichar - $wpjobportal_startCount[$wpjobportal_n]) * 2 + $wpjobportal_idRangeOffset[$wpjobportal_n];
                    $offset = $wpjobportal_idRangeOffset_start + 2 * $wpjobportal_n + $offset;
                    if ($offset >= $limit)
                        $glyph = 0;
                    else {
                        $glyph = $this->get_ushort($offset);
                        if ($glyph != 0)
                            $glyph = ($glyph + $wpjobportal_idDelta[$wpjobportal_n]) & 0xFFFF;
                    }
                }
                $charToGlyph[$unichar] = $glyph;
                if ($unichar < 196608) {
                    $this->maxUniChar = max($unichar, $this->maxUniChar);
                }
                $glyphToChar[$glyph][] = $unichar;
            }
        }
    }

    // Put the TTF file together
    function endTTFile(&$wpjobportal_stm) {
        $wpjobportal_stm = '';
        $wpjobportal_numTables = count($this->otables);
        $wpjobportal_searchRange = 1;
        $wpjobportal_entrySelector = 0;
        while ($wpjobportal_searchRange * 2 <= $wpjobportal_numTables) {
            $wpjobportal_searchRange = $wpjobportal_searchRange * 2;
            $wpjobportal_entrySelector = $wpjobportal_entrySelector + 1;
        }
        $wpjobportal_searchRange = $wpjobportal_searchRange * 16;
        $wpjobportal_rangeShift = $wpjobportal_numTables * 16 - $wpjobportal_searchRange;

        // Header
        if (_TTF_MAC_HEADER) {
            $wpjobportal_stm .= (pack("Nnnnn", 0x74727565, $wpjobportal_numTables, $wpjobportal_searchRange, $wpjobportal_entrySelector, $wpjobportal_rangeShift)); // Mac
        } else {
            $wpjobportal_stm .= (pack("Nnnnn", 0x00010000, $wpjobportal_numTables, $wpjobportal_searchRange, $wpjobportal_entrySelector, $wpjobportal_rangeShift)); // Windows
        }

        // Table directory
        $wpjobportal_tables = $this->otables;

        ksort($wpjobportal_tables);
        $offset = 12 + $wpjobportal_numTables * 16;
        foreach ($wpjobportal_tables AS $wpjobportal_tag => $wpjobportal_data) {
            if ($wpjobportal_tag == 'head') {
                $head_start = $offset;
            }
            $wpjobportal_stm .= $wpjobportal_tag;
            $wpjobportal_checksum = $this->calcChecksum($wpjobportal_data);
            $wpjobportal_stm .= pack("nn", $wpjobportal_checksum[0], $wpjobportal_checksum[1]);
            $wpjobportal_stm .= pack("NN", $offset, wpjobportalphplib::wpJP_strlen($wpjobportal_data));
            $wpjobportal_paddedLength = (wpjobportalphplib::wpJP_strlen($wpjobportal_data) + 3) & ~3;
            $offset = $offset + $wpjobportal_paddedLength;
        }

        // Table data
        foreach ($wpjobportal_tables AS $wpjobportal_tag => $wpjobportal_data) {
            $wpjobportal_data .= "\0\0\0";
            $wpjobportal_stm .= wpjobportalphplib::wpJP_substr($wpjobportal_data, 0, (wpjobportalphplib::wpJP_strlen($wpjobportal_data) & ~3));
        }

        $wpjobportal_checksum = $this->calcChecksum($wpjobportal_stm);
        $wpjobportal_checksum = $this->sub32(array(0xB1B0, 0xAFBA), $wpjobportal_checksum);
        $chk = pack("nn", $wpjobportal_checksum[0], $wpjobportal_checksum[1]);
        $wpjobportal_stm = $this->splice($wpjobportal_stm, ($head_start + 8), $chk);
        return $wpjobportal_stm;
    }

}

?>
