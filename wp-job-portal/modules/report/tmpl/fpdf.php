<?php

/* * *****************************************************************************
 * FPDF                                                                         *
 *                                                                              *
 * Version: 1.7                                                                 *
 * Date:    2011-06-18                                                          *
 * Author:  Olivier PLATHEY                                                     *
 * ***************************************************************************** */
if (!defined('ABSPATH'))
    die('Restricted Access');
define('FPDF_VERSION', '1.7');

class FPDF {

    var $page;               // current page number
    var $wpjobportal_n;                  // current object number
    var $offsets;            // array of object offsets
    var $buffer;             // buffer holding in-memory PDF
    var $pages;              // array containing pages
    var $wpjobportal_state;              // current document state
    var $wpjobportal_compress;           // compression flag
    var $wpjobportal_k;                  // scale factor (number of points in user unit)
    var $DefOrientation;     // default orientation
    var $CurOrientation;     // current orientation
    var $StdPageSizes;       // standard page sizes
    var $DefPageSize;        // default page size
    var $CurPageSize;        // current page size
    var $PageSizes;          // used for pages with non default sizes or orientations
    var $wPt, $hPt;          // dimensions of current page in points
    var $w, $h;              // dimensions of current page in user unit
    var $lMargin;            // left margin
    var $tMargin;            // top margin
    var $rMargin;            // right margin
    var $bMargin;            // page break margin
    var $cMargin;            // cell margin
    var $x, $y;              // current position in user unit
    var $lasth;              // height of last printed cell
    var $LineWidth;          // line width in user unit
    var $wpjobportal_fontpath;           // path containing fonts
    var $CoreFonts;          // array of core font names
    var $wpjobportal_fonts;              // array of used fonts
    var $FontFiles;          // array of font files
    var $wpjobportal_diffs;              // array of encoding differences
    var $FontFamily;         // current font family
    var $FontStyle;          // current font style
    var $underline;          // underlining flag
    var $CurrentFont;        // current font info
    var $FontSizePt;         // current font size in points
    var $FontSize;           // current font size in user unit
    var $DrawColor;          // commands for drawing color
    var $FillColor;          // commands for filling color
    var $TextColor;          // commands for text color
    var $ColorFlag;          // indicates whether fill and text colors are different
    var $ws;                 // word spacing
    var $wpjobportal_images;             // array of used images
    var $PageLinks;          // array of links in pages
    var $wpjobportal_links;              // array of internal links
    var $AutoPageBreak;      // automatic page breaking
    var $PageBreakTrigger;   // threshold used to trigger page breaks
    var $InHeader;           // flag set when processing header
    var $InFooter;           // flag set when processing footer
    var $ZoomMode;           // zoom display mode
    var $LayoutMode;         // layout display mode
    var $title;              // title
    var $wpjobportal_subject;            // subject
    var $author;             // author
    var $wpjobportal_keywords;           // keywords
    var $creator;            // creator
    var $AliasNbPages;       // alias for total number of pages
    var $PDFVersion;         // PDF version number

    /*     * *****************************************************************************
     *                                                                              *
     *                               Public methods                                 *
     *                                                                              *
     * ***************************************************************************** */

    function __construct($orientation = 'P', $unit = 'mm', $size = 'A4') {
        // Some checks
        $this->_dochecks();
        // Initialization of properties
        $this->page = 0;
        $this->n = 2;
        $this->buffer = '';
        $this->pages = array();
        $this->PageSizes = array();
        $this->state = 0;
        $this->fonts = array();
        $this->FontFiles = array();
        $this->diffs = array();
        $this->images = array();
        $this->links = array();
        $this->InHeader = false;
        $this->InFooter = false;
        $this->lasth = 0;
        $this->FontFamily = '';
        $this->FontStyle = '';
        $this->FontSizePt = 12;
        $this->underline = false;
        $this->DrawColor = '0 G';
        $this->FillColor = '0 g';
        $this->TextColor = '0 g';
        $this->ColorFlag = false;
        $this->ws = 0;
        // Font path
        if (defined('FPDF_FONTPATH')) {
            $this->fontpath = FPDF_FONTPATH;
            if (wpjobportalphplib::wpJP_substr($this->fontpath, -1) != '/' && wpjobportalphplib::wpJP_substr($this->fontpath, -1) != '\\')
                $this->fontpath .= '/';
        }
        elseif (is_dir(wpjobportalphplib::wpJP_dirname(__FILE__) . '/font'))
            $this->fontpath = wpjobportalphplib::wpJP_dirname(__FILE__) . '/font/';
        else
            $this->fontpath = '';
        // Core fonts
        $this->CoreFonts = array('courier', 'helvetica', 'times', 'symbol', 'zapfdingbats');
        // Scale factor
        if ($unit == 'pt')
            $this->k = 1;
        elseif ($unit == 'mm')
            $this->k = 72 / 25.4;
        elseif ($unit == 'cm')
            $this->k = 72 / 2.54;
        elseif ($unit == 'in')
            $this->k = 72;
        else
            $this->Error('Incorrect unit: ' . $unit);
        // Page sizes
        $this->StdPageSizes = array('a3' => array(841.89, 1190.55), 'a4' => array(595.28, 841.89), 'a5' => array(420.94, 595.28),
            'letter' => array(612, 792), 'legal' => array(612, 1008));
        $size = $this->_getpagesize($size);
        $this->DefPageSize = $size;
        $this->CurPageSize = $size;
        // Page orientation
        $orientation = wpjobportalphplib::wpJP_strtolower($orientation);
        if ($orientation == 'p' || $orientation == 'portrait') {
            $this->DefOrientation = 'P';
            $this->w = $size[0];
            $this->h = $size[1];
        } elseif ($orientation == 'l' || $orientation == 'landscape') {
            $this->DefOrientation = 'L';
            $this->w = $size[1];
            $this->h = $size[0];
        } else
            $this->Error('Incorrect orientation: ' . $orientation);
        $this->CurOrientation = $this->DefOrientation;
        $this->wPt = $this->w * $this->k;
        $this->hPt = $this->h * $this->k;
        // Page margins (1 cm)
        $wpjobportal_margin = 28.35 / $this->k;
        $this->SetMargins($wpjobportal_margin, $wpjobportal_margin);
        // Interior cell margin (1 mm)
        $this->cMargin = $wpjobportal_margin / 10;
        // Line width (0.2 mm)
        $this->LineWidth = .567 / $this->k;
        // Automatic page break
        $this->SetAutoPageBreak(true, 2 * $wpjobportal_margin);
        // Default display mode
        $this->SetDisplayMode('default');
        // Enable compression
        $this->SetCompression(true);
        // Set default PDF version number
        $this->PDFVersion = '1.3';
    }

    function SetMargins($left, $wpjobportal_top, $right = null) {
        // Set left, top and right margins
        $this->lMargin = $left;
        $this->tMargin = $wpjobportal_top;
        if ($right === null)
            $right = $left;
        $this->rMargin = $right;
    }

    function SetLeftMargin($wpjobportal_margin) {
        // Set left margin
        $this->lMargin = $wpjobportal_margin;
        if ($this->page > 0 && $this->x < $wpjobportal_margin)
            $this->x = $wpjobportal_margin;
    }

    function SetTopMargin($wpjobportal_margin) {
        // Set top margin
        $this->tMargin = $wpjobportal_margin;
    }

    function SetRightMargin($wpjobportal_margin) {
        // Set right margin
        $this->rMargin = $wpjobportal_margin;
    }

    function SetAutoPageBreak($auto, $wpjobportal_margin = 0) {
        // Set auto page break mode and triggering margin
        $this->AutoPageBreak = $auto;
        $this->bMargin = $wpjobportal_margin;
        $this->PageBreakTrigger = $this->h - $wpjobportal_margin;
    }

    function SetDisplayMode($zoom, $wpjobportal_layout = 'default') {
        // Set display mode in viewer
        if ($zoom == 'fullpage' || $zoom == 'fullwidth' || $zoom == 'real' || $zoom == 'default' || !is_string($zoom))
            $this->ZoomMode = $zoom;
        else
            $this->Error('Incorrect zoom display mode: ' . $zoom);
        if ($wpjobportal_layout == 'single' || $wpjobportal_layout == 'continuous' || $wpjobportal_layout == 'two' || $wpjobportal_layout == 'default')
            $this->LayoutMode = $wpjobportal_layout;
        else
            $this->Error('Incorrect layout display mode: ' . $wpjobportal_layout);
    }

    function SetCompression($wpjobportal_compress) {
        // Set page compression
        if (function_exists('gzcompress'))
            $this->compress = $wpjobportal_compress;
        else
            $this->compress = false;
    }

    function SetTitle($title, $wpjobportal_isUTF8 = false) {
        // Title of document
        if ($wpjobportal_isUTF8)
            $title = $this->_UTF8toUTF16($title);
        $this->title = $title;
    }

    function SetSubject($wpjobportal_subject, $wpjobportal_isUTF8 = false) {
        // Subject of document
        if ($wpjobportal_isUTF8)
            $wpjobportal_subject = $this->_UTF8toUTF16($wpjobportal_subject);
        $this->subject = $wpjobportal_subject;
    }

    function SetAuthor($author, $wpjobportal_isUTF8 = false) {
        // Author of document
        if ($wpjobportal_isUTF8)
            $author = $this->_UTF8toUTF16($author);
        $this->author = $author;
    }

    function SetKeywords($wpjobportal_keywords, $wpjobportal_isUTF8 = false) {
        // Keywords of document
        if ($wpjobportal_isUTF8)
            $wpjobportal_keywords = $this->_UTF8toUTF16($wpjobportal_keywords);
        $this->keywords = $wpjobportal_keywords;
    }

    function SetCreator($creator, $wpjobportal_isUTF8 = false) {
        // Creator of document
        if ($wpjobportal_isUTF8)
            $creator = $this->_UTF8toUTF16($creator);
        $this->creator = $creator;
    }

    function AliasNbPages($alias = '{nb}') {
        // Define an alias for total number of pages
        $this->AliasNbPages = $alias;
    }

    function Error($wpjobportal_msg) {
        // Fatal error
        die('<b>FPDF error:</b> ' . esc_html($wpjobportal_msg));
    }

    function Open() {
        // Begin document
        $this->state = 1;
    }

    function Close() {
        // Terminate document
        if ($this->state == 3)
            return;
        if ($this->page == 0)
            $this->AddPage();
        // Page footer
        $this->InFooter = true;
        $this->Footer();
        $this->InFooter = false;
        // Close page
        $this->_endpage();
        // Close document
        $this->_enddoc();
    }

    function AddPage($orientation = '', $size = '') {
        // Start a new page
        if ($this->state == 0)
            $this->Open();
        $family = $this->FontFamily;
        $wpjobportal_style = $this->FontStyle . ($this->underline ? 'U' : '');
        $wpjobportal_fontsize = $this->FontSizePt;
        $lw = $this->LineWidth;
        $dc = $this->DrawColor;
        $fc = $this->FillColor;
        $tc = $this->TextColor;
        $cf = $this->ColorFlag;
        if ($this->page > 0) {
            // Page footer
            $this->InFooter = true;
            $this->Footer();
            $this->InFooter = false;
            // Close page
            $this->_endpage();
        }
        // Start new page
        $this->_beginpage($orientation, $size);
        // Set line cap style to square
        $this->_out('2 J');
        // Set line width
        $this->LineWidth = $lw;
        $this->_out(sprintf('%.2F w', $lw * $this->k));
        // Set font
        if ($family)
            $this->SetFont($family, $wpjobportal_style, $wpjobportal_fontsize);
        // Set colors
        $this->DrawColor = $dc;
        if ($dc != '0 G')
            $this->_out($dc);
        $this->FillColor = $fc;
        if ($fc != '0 g')
            $this->_out($fc);
        $this->TextColor = $tc;
        $this->ColorFlag = $cf;
        // Page header
        $this->InHeader = true;
        $this->Header();
        $this->InHeader = false;
        // Restore line width
        if ($this->LineWidth != $lw) {
            $this->LineWidth = $lw;
            $this->_out(sprintf('%.2F w', $lw * $this->k));
        }
        // Restore font
        if ($family)
            $this->SetFont($family, $wpjobportal_style, $wpjobportal_fontsize);
        // Restore colors
        if ($this->DrawColor != $dc) {
            $this->DrawColor = $dc;
            $this->_out($dc);
        }
        if ($this->FillColor != $fc) {
            $this->FillColor = $fc;
            $this->_out($fc);
        }
        $this->TextColor = $tc;
        $this->ColorFlag = $cf;
    }

    function Header() {
        // To be implemented in your own inherited class
    }

    function Footer() {
        // To be implemented in your own inherited class
    }

    function PageNo() {
        // Get current page number
        return $this->page;
    }

    function SetDrawColor($r, $g = null, $b = null) {
        // Set color for all stroking operations
        if (($r == 0 && $g == 0 && $b == 0) || $g === null)
            $this->DrawColor = sprintf('%.3F G', $r / 255);
        else
            $this->DrawColor = sprintf('%.3F %.3F %.3F RG', $r / 255, $g / 255, $b / 255);
        if ($this->page > 0)
            $this->_out($this->DrawColor);
    }

    function SetFillColor($r, $g = null, $b = null) {
        // Set color for all filling operations
        if (($r == 0 && $g == 0 && $b == 0) || $g === null)
            $this->FillColor = sprintf('%.3F g', $r / 255);
        else
            $this->FillColor = sprintf('%.3F %.3F %.3F rg', $r / 255, $g / 255, $b / 255);
        $this->ColorFlag = ($this->FillColor != $this->TextColor);
        if ($this->page > 0)
            $this->_out($this->FillColor);
    }

    function SetTextColor($r, $g = null, $b = null) {
        // Set color for text
        if (($r == 0 && $g == 0 && $b == 0) || $g === null)
            $this->TextColor = sprintf('%.3F g', $r / 255);
        else
            $this->TextColor = sprintf('%.3F %.3F %.3F rg', $r / 255, $g / 255, $b / 255);
        $this->ColorFlag = ($this->FillColor != $this->TextColor);
    }

    function GetStringWidth($s) {
        // Get width of a string in the current font
        $s = (string) $s;
        $wpjobportal_cw = &$this->CurrentFont['cw'];
        $w = 0;
        $l = wpjobportalphplib::wpJP_strlen($s);
        for ($wpjobportal_i = 0; $wpjobportal_i < $l; $wpjobportal_i++)
            $w += $wpjobportal_cw[$s[$wpjobportal_i]];
        return $w * $this->FontSize / 1000;
    }

    function SetLineWidth($wpjobportal_width) {
        // Set line width
        $this->LineWidth = $wpjobportal_width;
        if ($this->page > 0)
            $this->_out(sprintf('%.2F w', $wpjobportal_width * $this->k));
    }

    function Line($x1, $y1, $x2, $y2) {
        // Draw a line
        $this->_out(sprintf('%.2F %.2F m %.2F %.2F l S', $x1 * $this->k, ($this->h - $y1) * $this->k, $x2 * $this->k, ($this->h - $y2) * $this->k));
    }

    function Rect($x, $y, $w, $h, $wpjobportal_style = '') {
        // Draw a rectangle
        if ($wpjobportal_style == 'F')
            $op = 'f';
        elseif ($wpjobportal_style == 'FD' || $wpjobportal_style == 'DF')
            $op = 'B';
        else
            $op = 'S';
        $this->_out(sprintf('%.2F %.2F %.2F %.2F re %s', $x * $this->k, ($this->h - $y) * $this->k, $w * $this->k, -$h * $this->k, $op));
    }

    function AddFont($family, $wpjobportal_style = '', $file = '') {
        // Add a TrueType, OpenType or Type1 font
        $family = wpjobportalphplib::wpJP_strtolower($family);
        if ($file == '')
            $file = wpjobportalphplib::wpJP_str_replace(' ', '', $family) . wpjobportalphplib::wpJP_strtolower($wpjobportal_style) . '.php';
        $wpjobportal_style = wpjobportalphplib::wpJP_strtoupper($wpjobportal_style);
        if ($wpjobportal_style == 'IB')
            $wpjobportal_style = 'BI';
        $wpjobportal_fontkey = $family . $wpjobportal_style;
        if (isset($this->fonts[$wpjobportal_fontkey]))
            return;
        $wpjobportal_info = $this->_loadfont($file);
        $wpjobportal_info['i'] = count($this->fonts) + 1;
        if (!empty($wpjobportal_info['diff'])) {
            // Search existing encodings
            $wpjobportal_n = array_search($wpjobportal_info['diff'], $this->diffs);
            if (!$wpjobportal_n) {
                $wpjobportal_n = count($this->diffs) + 1;
                $this->diffs[$wpjobportal_n] = $wpjobportal_info['diff'];
            }
            $wpjobportal_info['diffn'] = $wpjobportal_n;
        }
        if (!empty($wpjobportal_info['file'])) {
            // Embedded font
            if ($wpjobportal_info['type'] == 'TrueType')
                $this->FontFiles[$wpjobportal_info['file']] = array('length1' => $wpjobportal_info['originalsize']);
            else
                $this->FontFiles[$wpjobportal_info['file']] = array('length1' => $wpjobportal_info['size1'], 'length2' => $wpjobportal_info['size2']);
        }
        $this->fonts[$wpjobportal_fontkey] = $wpjobportal_info;
    }

    function SetFont($family, $wpjobportal_style = '', $size = 0) {
        // Select a font; size given in points
        if ($family == '')
            $family = $this->FontFamily;
        else
            $family = wpjobportalphplib::wpJP_strtolower($family);
        $wpjobportal_style = wpjobportalphplib::wpJP_strtoupper($wpjobportal_style);
        if (wpjobportalphplib::wpJP_strpos($wpjobportal_style, 'U') !== false) {
            $this->underline = true;
            $wpjobportal_style = wpjobportalphplib::wpJP_str_replace('U', '', $wpjobportal_style);
        } else
            $this->underline = false;
        if ($wpjobportal_style == 'IB')
            $wpjobportal_style = 'BI';
        if ($size == 0)
            $size = $this->FontSizePt;
        // Test if font is already selected
        if ($this->FontFamily == $family && $this->FontStyle == $wpjobportal_style && $this->FontSizePt == $size)
            return;
        // Test if font is already loaded
        $wpjobportal_fontkey = $family . $wpjobportal_style;
        if (!isset($this->fonts[$wpjobportal_fontkey])) {
            // Test if one of the core fonts
            if ($family == 'arial')
                $family = 'helvetica';
            if (in_array($family, $this->CoreFonts)) {
                if ($family == 'symbol' || $family == 'zapfdingbats')
                    $wpjobportal_style = '';
                $wpjobportal_fontkey = $family . $wpjobportal_style;
                if (!isset($this->fonts[$wpjobportal_fontkey]))
                    $this->AddFont($family, $wpjobportal_style);
            } else
                $this->Error('Undefined font: ' . $family . ' ' . $wpjobportal_style);
        }
        // Select it
        $this->FontFamily = $family;
        $this->FontStyle = $wpjobportal_style;
        $this->FontSizePt = $size;
        $this->FontSize = $size / $this->k;
        $this->CurrentFont = &$this->fonts[$wpjobportal_fontkey];
        if ($this->page > 0)
            $this->_out(sprintf('BT /F%d %.2F Tf ET', $this->CurrentFont['i'], $this->FontSizePt));
    }

    function SetFontSize($size) {
        // Set font size in points
        if ($this->FontSizePt == $size)
            return;
        $this->FontSizePt = $size;
        $this->FontSize = $size / $this->k;
        if ($this->page > 0)
            $this->_out(sprintf('BT /F%d %.2F Tf ET', $this->CurrentFont['i'], $this->FontSizePt));
    }

    function AddLink() {
        // Create a new internal link
        $wpjobportal_n = count($this->links) + 1;
        $this->links[$wpjobportal_n] = array(0, 0);
        return $wpjobportal_n;
    }

    function SetLink($wpjobportal_link, $y = 0, $page = -1) {
        // Set destination of internal link
        if ($y == -1)
            $y = $this->y;
        if ($page == -1)
            $page = $this->page;
        $this->links[$wpjobportal_link] = array($page, $y);
    }

    function Link($x, $y, $w, $h, $wpjobportal_link) {
        // Put a link on the page
        $this->PageLinks[$this->page][] = array($x * $this->k, $this->hPt - $y * $this->k, $w * $this->k, $h * $this->k, $wpjobportal_link);
    }

    function Text($x, $y, $txt) {
        // Output a string
        $s = sprintf('BT %.2F %.2F Td (%s) Tj ET', $x * $this->k, ($this->h - $y) * $this->k, $this->_escape($txt));
        if ($this->underline && $txt != '')
            $s .= ' ' . $this->_dounderline($x, $y, $txt);
        if ($this->ColorFlag)
            $s = 'q ' . $this->TextColor . ' ' . $s . ' Q';
        $this->_out($s);
    }

    function AcceptPageBreak() {
        // Accept automatic page break or not
        return $this->AutoPageBreak;
    }

    function Cell($w, $h = 0, $txt = '', $border = 0, $ln = 0, $align = '', $fill = false, $wpjobportal_link = '') {
        // Output a cell
        $wpjobportal_k = $this->k;
        if ($this->y + $h > $this->PageBreakTrigger && !$this->InHeader && !$this->InFooter && $this->AcceptPageBreak()) {
            // Automatic page break
            $x = $this->x;
            $ws = $this->ws;
            if ($ws > 0) {
                $this->ws = 0;
                $this->_out('0 Tw');
            }
            $this->AddPage($this->CurOrientation, $this->CurPageSize);
            $this->x = $x;
            if ($ws > 0) {
                $this->ws = $ws;
                $this->_out(sprintf('%.3F Tw', $ws * $wpjobportal_k));
            }
        }
        if ($w == 0)
            $w = $this->w - $this->rMargin - $this->x;
        $s = '';
        if ($fill || $border == 1) {
            if ($fill)
                $op = ($border == 1) ? 'B' : 'f';
            else
                $op = 'S';
            $s = sprintf('%.2F %.2F %.2F %.2F re %s ', $this->x * $wpjobportal_k, ($this->h - $this->y) * $wpjobportal_k, $w * $wpjobportal_k, -$h * $wpjobportal_k, $op);
        }
        if (is_string($border)) {
            $x = $this->x;
            $y = $this->y;
            if (wpjobportalphplib::wpJP_strpos($border, 'L') !== false)
                $s .= sprintf('%.2F %.2F m %.2F %.2F l S ', $x * $wpjobportal_k, ($this->h - $y) * $wpjobportal_k, $x * $wpjobportal_k, ($this->h - ($y + $h)) * $wpjobportal_k);
            if (wpjobportalphplib::wpJP_strpos($border, 'T') !== false)
                $s .= sprintf('%.2F %.2F m %.2F %.2F l S ', $x * $wpjobportal_k, ($this->h - $y) * $wpjobportal_k, ($x + $w) * $wpjobportal_k, ($this->h - $y) * $wpjobportal_k);
            if (wpjobportalphplib::wpJP_strpos($border, 'R') !== false)
                $s .= sprintf('%.2F %.2F m %.2F %.2F l S ', ($x + $w) * $wpjobportal_k, ($this->h - $y) * $wpjobportal_k, ($x + $w) * $wpjobportal_k, ($this->h - ($y + $h)) * $wpjobportal_k);
            if (wpjobportalphplib::wpJP_strpos($border, 'B') !== false)
                $s .= sprintf('%.2F %.2F m %.2F %.2F l S ', $x * $wpjobportal_k, ($this->h - ($y + $h)) * $wpjobportal_k, ($x + $w) * $wpjobportal_k, ($this->h - ($y + $h)) * $wpjobportal_k);
        }
        if ($txt !== '') {
            if ($align == 'R')
                $dx = $w - $this->cMargin - $this->GetStringWidth($txt);
            elseif ($align == 'C')
                $dx = ($w - $this->GetStringWidth($txt)) / 2;
            else
                $dx = $this->cMargin;
            if ($this->ColorFlag)
                $s .= 'q ' . $this->TextColor . ' ';
            $txt2 = wpjobportalphplib::wpJP_str_replace(')', '\\)', wpjobportalphplib::wpJP_str_replace('(', '\\(', wpjobportalphplib::wpJP_str_replace('\\', '\\\\', $txt)));
            $s .= sprintf('BT %.2F %.2F Td (%s) Tj ET', ($this->x + $dx) * $wpjobportal_k, ($this->h - ($this->y + .5 * $h + .3 * $this->FontSize)) * $wpjobportal_k, $txt2);
            if ($this->underline)
                $s .= ' ' . $this->_dounderline($this->x + $dx, $this->y + .5 * $h + .3 * $this->FontSize, $txt);
            if ($this->ColorFlag)
                $s .= ' Q';
            if ($wpjobportal_link)
                $this->Link($this->x + $dx, $this->y + .5 * $h - .5 * $this->FontSize, $this->GetStringWidth($txt), $this->FontSize, $wpjobportal_link);
        }
        if ($s)
            $this->_out($s);
        $this->lasth = $h;
        if ($ln > 0) {
            // Go to next line
            $this->y += $h;
            if ($ln == 1)
                $this->x = $this->lMargin;
        } else
            $this->x += $w;
    }

    function MultiCell($w, $h, $txt, $border = 0, $align = 'J', $fill = false) {
        // Output text with automatic or explicit line breaks
        $wpjobportal_cw = &$this->CurrentFont['cw'];
        if ($w == 0)
            $w = $this->w - $this->rMargin - $this->x;
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = wpjobportalphplib::wpJP_str_replace("\r", '', $txt);
        $wpjobportal_nb = wpjobportalphplib::wpJP_strlen($s);
        if ($wpjobportal_nb > 0 && $s[$wpjobportal_nb - 1] == "\n")
            $wpjobportal_nb--;
        $b = 0;
        if ($border) {
            if ($border == 1) {
                $border = 'LTRB';
                $b = 'LRT';
                $b2 = 'LR';
            } else {
                $b2 = '';
                if (wpjobportalphplib::wpJP_strpos($border, 'L') !== false)
                    $b2 .= 'L';
                if (wpjobportalphplib::wpJP_strpos($border, 'R') !== false)
                    $b2 .= 'R';
                $b = (wpjobportalphplib::wpJP_strpos($border, 'T') !== false) ? $b2 . 'T' : $b2;
            }
        }
        $wpjobportal_sep = -1;
        $wpjobportal_i = 0;
        $j = 0;
        $l = 0;
        $wpjobportal_ns = 0;
        $wpjobportal_nl = 1;
        while ($wpjobportal_i < $wpjobportal_nb) {
            // Get next character
            $c = $s[$wpjobportal_i];
            if ($c == "\n") {
                // Explicit line break
                if ($this->ws > 0) {
                    $this->ws = 0;
                    $this->_out('0 Tw');
                }
                $this->Cell($w, $h, wpjobportalphplib::wpJP_substr($s, $j, $wpjobportal_i - $j), $b, 2, $align, $fill);
                $wpjobportal_i++;
                $wpjobportal_sep = -1;
                $j = $wpjobportal_i;
                $l = 0;
                $wpjobportal_ns = 0;
                $wpjobportal_nl++;
                if ($border && $wpjobportal_nl == 2)
                    $b = $b2;
                continue;
            }
            if ($c == ' ') {
                $wpjobportal_sep = $wpjobportal_i;
                $ls = $l;
                $wpjobportal_ns++;
            }
            $l += $wpjobportal_cw[$c];
            if ($l > $wmax) {
                // Automatic line break
                if ($wpjobportal_sep == -1) {
                    if ($wpjobportal_i == $j)
                        $wpjobportal_i++;
                    if ($this->ws > 0) {
                        $this->ws = 0;
                        $this->_out('0 Tw');
                    }
                    $this->Cell($w, $h, wpjobportalphplib::wpJP_substr($s, $j, $wpjobportal_i - $j), $b, 2, $align, $fill);
                } else {
                    if ($align == 'J') {
                        $this->ws = ($wpjobportal_ns > 1) ? ($wmax - $ls) / 1000 * $this->FontSize / ($wpjobportal_ns - 1) : 0;
                        $this->_out(sprintf('%.3F Tw', $this->ws * $this->k));
                    }
                    $this->Cell($w, $h, wpjobportalphplib::wpJP_substr($s, $j, $wpjobportal_sep - $j), $b, 2, $align, $fill);
                    $wpjobportal_i = $wpjobportal_sep + 1;
                }
                $wpjobportal_sep = -1;
                $j = $wpjobportal_i;
                $l = 0;
                $wpjobportal_ns = 0;
                $wpjobportal_nl++;
                if ($border && $wpjobportal_nl == 2)
                    $b = $b2;
            } else
                $wpjobportal_i++;
        }
        // Last chunk
        if ($this->ws > 0) {
            $this->ws = 0;
            $this->_out('0 Tw');
        }
        if ($border && wpjobportalphplib::wpJP_strpos($border, 'B') !== false)
            $b .= 'B';
        $this->Cell($w, $h, wpjobportalphplib::wpJP_substr($s, $j, $wpjobportal_i - $j), $b, 2, $align, $fill);
        $this->x = $this->lMargin;
    }

    function Write($h, $txt, $wpjobportal_link = '') {
        // Output text in flowing mode
        $wpjobportal_cw = &$this->CurrentFont['cw'];
        $w = $this->w - $this->rMargin - $this->x;
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = wpjobportalphplib::wpJP_str_replace("\r", '', $txt);
        $wpjobportal_nb = wpjobportalphplib::wpJP_strlen($s);
        $wpjobportal_sep = -1;
        $wpjobportal_i = 0;
        $j = 0;
        $l = 0;
        $wpjobportal_nl = 1;
        while ($wpjobportal_i < $wpjobportal_nb) {
            // Get next character
            $c = $s[$wpjobportal_i];
            if ($c == "\n") {
                // Explicit line break
                $this->Cell($w, $h, wpjobportalphplib::wpJP_substr($s, $j, $wpjobportal_i - $j), 0, 2, '', 0, $wpjobportal_link);
                $wpjobportal_i++;
                $wpjobportal_sep = -1;
                $j = $wpjobportal_i;
                $l = 0;
                if ($wpjobportal_nl == 1) {
                    $this->x = $this->lMargin;
                    $w = $this->w - $this->rMargin - $this->x;
                    $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
                }
                $wpjobportal_nl++;
                continue;
            }
            if ($c == ' ')
                $wpjobportal_sep = $wpjobportal_i;
            $l += $wpjobportal_cw[$c];
            if ($l > $wmax) {
                // Automatic line break
                if ($wpjobportal_sep == -1) {
                    if ($this->x > $this->lMargin) {
                        // Move to next line
                        $this->x = $this->lMargin;
                        $this->y += $h;
                        $w = $this->w - $this->rMargin - $this->x;
                        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
                        $wpjobportal_i++;
                        $wpjobportal_nl++;
                        continue;
                    }
                    if ($wpjobportal_i == $j)
                        $wpjobportal_i++;
                    $this->Cell($w, $h, wpjobportalphplib::wpJP_substr($s, $j, $wpjobportal_i - $j), 0, 2, '', 0, $wpjobportal_link);
                }
                else {
                    $this->Cell($w, $h, wpjobportalphplib::wpJP_substr($s, $j, $wpjobportal_sep - $j), 0, 2, '', 0, $wpjobportal_link);
                    $wpjobportal_i = $wpjobportal_sep + 1;
                }
                $wpjobportal_sep = -1;
                $j = $wpjobportal_i;
                $l = 0;
                if ($wpjobportal_nl == 1) {
                    $this->x = $this->lMargin;
                    $w = $this->w - $this->rMargin - $this->x;
                    $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
                }
                $wpjobportal_nl++;
            } else
                $wpjobportal_i++;
        }
        // Last chunk
        if ($wpjobportal_i != $j)
            $this->Cell($l / 1000 * $this->FontSize, $h, wpjobportalphplib::wpJP_substr($s, $j), 0, 0, '', 0, $wpjobportal_link);
    }

    function Ln($h = null) {
        // Line feed; default value is last cell height
        $this->x = $this->lMargin;
        if ($h === null)
            $this->y += $this->lasth;
        else
            $this->y += $h;
    }

    function Image($file, $x = null, $y = null, $w = 0, $h = 0, $type = '', $wpjobportal_link = '') {
        // Put an image on the page
        if (!isset($this->images[$file])) {
            // First use of this image, get info
            if ($type == '') {
                $pos = strrpos($file, '.');
                if (!$pos)
                    $this->Error('Image file has no extension and no type was specified: ' . $file);
                $type = wpjobportalphplib::wpJP_substr($file, $pos + 1);
            }
            $type = wpjobportalphplib::wpJP_strtolower($type);
            if ($type == 'jpeg')
                $type = 'jpg';
            $mtd = '_parse' . $type;
            if (!method_exists($this, $mtd))
                $this->Error('Unsupported image type: ' . $type);
            $wpjobportal_info = $this->$mtd($file);
            $wpjobportal_info['i'] = count($this->images) + 1;
            $this->images[$file] = $wpjobportal_info;
        } else
            $wpjobportal_info = $this->images[$file];

        // Automatic width and height calculation if needed
        if ($w == 0 && $h == 0) {
            // Put image at 96 dpi
            $w = -96;
            $h = -96;
        }
        if ($w < 0)
            $w = -$wpjobportal_info['w'] * 72 / $w / $this->k;
        if ($h < 0)
            $h = -$wpjobportal_info['h'] * 72 / $h / $this->k;
        if ($w == 0)
            $w = $h * $wpjobportal_info['w'] / $wpjobportal_info['h'];
        if ($h == 0)
            $h = $w * $wpjobportal_info['h'] / $wpjobportal_info['w'];

        // Flowing mode
        if ($y === null) {
            if ($this->y + $h > $this->PageBreakTrigger && !$this->InHeader && !$this->InFooter && $this->AcceptPageBreak()) {
                // Automatic page break
                $x2 = $this->x;
                $this->AddPage($this->CurOrientation, $this->CurPageSize);
                $this->x = $x2;
            }
            $y = $this->y;
            $this->y += $h;
        }

        if ($x === null)
            $x = $this->x;
        $this->_out(sprintf('q %.2F 0 0 %.2F %.2F %.2F cm /I%d Do Q', $w * $this->k, $h * $this->k, $x * $this->k, ($this->h - ($y + $h)) * $this->k, $wpjobportal_info['i']));
        if ($wpjobportal_link)
            $this->Link($x, $y, $w, $h, $wpjobportal_link);
    }

    function GetX() {
        // Get x position
        return $this->x;
    }

    function SetX($x) {
        // Set x position
        if ($x >= 0)
            $this->x = $x;
        else
            $this->x = $this->w + $x;
    }

    function GetY() {
        // Get y position
        return $this->y;
    }

    function SetY($y) {
        // Set y position and reset x
        $this->x = $this->lMargin;
        if ($y >= 0)
            $this->y = $y;
        else
            $this->y = $this->h + $y;
    }

    function SetXY($x, $y) {
        // Set x and y positions
        $this->SetY($y);
        $this->SetX($x);
    }

    function Output($wpjobportal_name = '', $wpjobportal_dest = '') {
        // Output PDF to some destination
        if ($this->state < 3)
            $this->Close();
        $wpjobportal_dest = wpjobportalphplib::wpJP_strtoupper($wpjobportal_dest);
        if ($wpjobportal_dest == '') {
            if ($wpjobportal_name == '') {
                $wpjobportal_name = 'doc.pdf';
                $wpjobportal_dest = 'I';
            } else
                $wpjobportal_dest = 'I';
        }
        switch ($wpjobportal_dest) {
            case 'I':
                // Send to standard output
                $this->_checkoutput();
                if (PHP_SAPI != 'cli') {
                    // We send to a browser
                    header('Content-Type: application/pdf');
                    header('Content-Disposition: inline; filename="' . $wpjobportal_name . '"');
                    header('Cache-Control: private, max-age=0, must-revalidate');
                    header('Pragma: public');
                }
                echo esc_attr($this->buffer);
                break;
            case 'D':
                // Download file
                $this->_checkoutput();
                header('Content-Type: application/x-download');
                header('Content-Disposition: attachment; filename="' . $wpjobportal_name . '"');
                header('Cache-Control: private, max-age=0, must-revalidate');
                header('Pragma: public');
                echo esc_attr($this->buffer);
                break;
            case 'F':
                // Save to local file
                $f = fopen($wpjobportal_name, 'wb');
                if (!$f)
                    $this->Error('Unable to create output file: ' . $wpjobportal_name);
                fwrite($f, $this->buffer, wpjobportalphplib::wpJP_strlen($this->buffer));
                fclose($f);
                break;
            case 'S':
                // Return as a string
                return $this->buffer;
            default:
                $this->Error('Incorrect output destination: ' . $wpjobportal_dest);
        }
        return '';
    }

    /*     * *****************************************************************************
     *                                                                              *
     *                              Protected methods                               *
     *                                                                              *
     * ***************************************************************************** */

    function _dochecks() {
        // Check availability of %F
        if (sprintf('%.1F', 1.0) != '1.0')
            $this->Error('This version of PHP is not supported');
        // Check mbstring overloading
        if (ini_get('mbstring.func_overload') & 2)
            $this->Error('mbstring overloading must be disabled');
        // Ensure runtime magic quotes are disabled
        /* if (get_magic_quotes_runtime())
             @set_magic_quotes_runtime(0);*/
    }

    function _checkoutput() {
        if (PHP_SAPI != 'cli') {
            if (headers_sent($file, $line))
                $this->Error("Some data has already been output, can't send PDF file (output started at $file:$line)");
        }
        if (ob_get_length()) {
            // The output buffer is not empty
            if (wpjobportalphplib::wpJP_preg_match('/^(\xEF\xBB\xBF)?\s*$/', ob_get_contents())) {
                // It contains only a UTF-8 BOM and/or whitespace, let's clean it
                ob_clean();
            } else
                $this->Error("Some data has already been output, can't send PDF file");
        }
    }

    function _getpagesize($size) {
        if (is_string($size)) {
            $size = wpjobportalphplib::wpJP_strtolower($size);
            if (!isset($this->StdPageSizes[$size]))
                $this->Error('Unknown page size: ' . $size);
            $a = $this->StdPageSizes[$size];
            return array($a[0] / $this->k, $a[1] / $this->k);
        }
        else {
            if ($size[0] > $size[1])
                return array($size[1], $size[0]);
            else
                return $size;
        }
    }

    function _beginpage($orientation, $size) {
        $this->page++;
        $this->pages[$this->page] = '';
        $this->state = 2;
        $this->x = $this->lMargin;
        $this->y = $this->tMargin;
        $this->FontFamily = '';
        // Check page size and orientation
        if ($orientation == '')
            $orientation = $this->DefOrientation;
        else
            $orientation = wpjobportalphplib::wpJP_strtoupper($orientation[0]);
        if ($size == '')
            $size = $this->DefPageSize;
        else
            $size = $this->_getpagesize($size);
        if ($orientation != $this->CurOrientation || $size[0] != $this->CurPageSize[0] || $size[1] != $this->CurPageSize[1]) {
            // New size or orientation
            if ($orientation == 'P') {
                $this->w = $size[0];
                $this->h = $size[1];
            } else {
                $this->w = $size[1];
                $this->h = $size[0];
            }
            $this->wPt = $this->w * $this->k;
            $this->hPt = $this->h * $this->k;
            $this->PageBreakTrigger = $this->h - $this->bMargin;
            $this->CurOrientation = $orientation;
            $this->CurPageSize = $size;
        }
        if ($orientation != $this->DefOrientation || $size[0] != $this->DefPageSize[0] || $size[1] != $this->DefPageSize[1])
            $this->PageSizes[$this->page] = array($this->wPt, $this->hPt);
    }

    function _endpage() {
        $this->state = 1;
    }

    function _loadfont($wpjobportal_font) {
        // Load a font definition file from the font directory
        include($this->fontpath . $wpjobportal_font);
        $a = get_defined_vars();
        if (!isset($a['name']))
            $this->Error('Could not include font definition file');
        return $a;
    }

    function _escape($s) {
        // Escape special characters in strings
        $s = wpjobportalphplib::wpJP_str_replace('\\', '\\\\', $s);
        $s = wpjobportalphplib::wpJP_str_replace('(', '\\(', $s);
        $s = wpjobportalphplib::wpJP_str_replace(')', '\\)', $s);
        $s = wpjobportalphplib::wpJP_str_replace("\r", '\\r', $s);
        return $s;
    }

    function _textstring($s) {
        // Format a text string
        return '(' . $this->_escape($s) . ')';
    }

    function _UTF8toUTF16($s) {
        // Convert UTF-8 to UTF-16BE with BOM
        $res = "\xFE\xFF";
        $wpjobportal_nb = wpjobportalphplib::wpJP_strlen($s);
        $wpjobportal_i = 0;
        while ($wpjobportal_i < $wpjobportal_nb) {
            $c1 = ord($s[$wpjobportal_i++]);
            if ($c1 >= 224) {
                // 3-byte character
                $c2 = ord($s[$wpjobportal_i++]);
                $c3 = ord($s[$wpjobportal_i++]);
                $res .= chr((($c1 & 0x0F) << 4) + (($c2 & 0x3C) >> 2));
                $res .= chr((($c2 & 0x03) << 6) + ($c3 & 0x3F));
            } elseif ($c1 >= 192) {
                // 2-byte character
                $c2 = ord($s[$wpjobportal_i++]);
                $res .= chr(($c1 & 0x1C) >> 2);
                $res .= chr((($c1 & 0x03) << 6) + ($c2 & 0x3F));
            } else {
                // Single-byte character
                $res .= "\0" . chr($c1);
            }
        }
        return $res;
    }

    function _dounderline($x, $y, $txt) {
        // Underline text
        $wpjobportal_up = $this->CurrentFont['up'];
        $wpjobportal_ut = $this->CurrentFont['ut'];
        $w = $this->GetStringWidth($txt) + $this->ws * substr_count($txt, ' ');
        return sprintf('%.2F %.2F %.2F %.2F re f', $x * $this->k, ($this->h - ($y - $wpjobportal_up / 1000 * $this->FontSize)) * $this->k, $w * $this->k, -$wpjobportal_ut / 1000 * $this->FontSizePt);
    }

    function _parsejpg($file) {
        // Extract info from a JPEG file
        $a = getimagesize($file);
        if (!$a)
            $this->Error('Missing or incorrect image file: ' . $file);
        if ($a[2] != 2)
            $this->Error('Not a JPEG file: ' . $file);
        if (!isset($a['channels']) || $a['channels'] == 3)
            $colspace = 'DeviceRGB';
        elseif ($a['channels'] == 4)
            $colspace = 'DeviceCMYK';
        else
            $colspace = 'DeviceGray';
        $bpc = isset($a['bits']) ? $a['bits'] : 8;
        $wpjobportal_data = wp_remote_get($file);
        if (is_wp_error($wpjobportal_data)) {
            $wpjobportal_data = '';
        }
        return array('w' => $a[0], 'h' => $a[1], 'cs' => $colspace, 'bpc' => $bpc, 'f' => 'DCTDecode', 'data' => $wpjobportal_data);
    }

    function _parsepng($file) {
        // Extract info from a PNG file
        $f = fopen($file, 'rb');
        if (!$f)
            $this->Error('Can\'t open image file: ' . $file);
        $wpjobportal_info = $this->_parsepngstream($f, $file);
        fclose($f);
        return $wpjobportal_info;
    }

    function _parsepngstream($f, $file) {
        // Check signature
        if ($this->_readstream($f, 8) != chr(137) . 'PNG' . chr(13) . chr(10) . chr(26) . chr(10))
            $this->Error('Not a PNG file: ' . $file);

        // Read header chunk
        $this->_readstream($f, 4);
        if ($this->_readstream($f, 4) != 'IHDR')
            $this->Error('Incorrect PNG file: ' . $file);
        $w = $this->_readint($f);
        $h = $this->_readint($f);
        $bpc = ord($this->_readstream($f, 1));
        if ($bpc > 8)
            $this->Error('16-bit depth not supported: ' . $file);
        $ct = ord($this->_readstream($f, 1));
        if ($ct == 0 || $ct == 4)
            $colspace = 'DeviceGray';
        elseif ($ct == 2 || $ct == 6)
            $colspace = 'DeviceRGB';
        elseif ($ct == 3)
            $colspace = 'Indexed';
        else
            $this->Error('Unknown color type: ' . $file);
        if (ord($this->_readstream($f, 1)) != 0)
            $this->Error('Unknown compression method: ' . $file);
        if (ord($this->_readstream($f, 1)) != 0)
            $this->Error('Unknown filter method: ' . $file);
        if (ord($this->_readstream($f, 1)) != 0)
            $this->Error('Interlacing not supported: ' . $file);
        $this->_readstream($f, 4);
        $dp = '/Predictor 15 /Colors ' . ($colspace == 'DeviceRGB' ? 3 : 1) . ' /BitsPerComponent ' . $bpc . ' /Columns ' . $w;

        // Scan chunks looking for palette, transparency and image data
        $pal = '';
        $trns = '';
        $wpjobportal_data = '';
        do {
            $wpjobportal_n = $this->_readint($f);
            $type = $this->_readstream($f, 4);
            if ($type == 'PLTE') {
                // Read palette
                $pal = $this->_readstream($f, $wpjobportal_n);
                $this->_readstream($f, 4);
            } elseif ($type == 'tRNS') {
                // Read transparency info
                $t = $this->_readstream($f, $wpjobportal_n);
                if ($ct == 0)
                    $trns = array(ord(wpjobportalphplib::wpJP_substr($t, 1, 1)));
                elseif ($ct == 2)
                    $trns = array(ord(wpjobportalphplib::wpJP_substr($t, 1, 1)), ord(wpjobportalphplib::wpJP_substr($t, 3, 1)), ord(wpjobportalphplib::wpJP_substr($t, 5, 1)));
                else {
                    $pos = wpjobportalphplib::wpJP_strpos($t, chr(0));
                    if ($pos !== false)
                        $trns = array($pos);
                }
                $this->_readstream($f, 4);
            }
            elseif ($type == 'IDAT') {
                // Read image data block
                $wpjobportal_data .= $this->_readstream($f, $wpjobportal_n);
                $this->_readstream($f, 4);
            } elseif ($type == 'IEND')
                break;
            else
                $this->_readstream($f, $wpjobportal_n + 4);
        }
        while ($wpjobportal_n);

        if ($colspace == 'Indexed' && empty($pal))
            $this->Error('Missing palette in ' . $file);
        $wpjobportal_info = array('w' => $w, 'h' => $h, 'cs' => $colspace, 'bpc' => $bpc, 'f' => 'FlateDecode', 'dp' => $dp, 'pal' => $pal, 'trns' => $trns);
        if ($ct >= 4) {
            // Extract alpha channel
            if (!function_exists('gzuncompress'))
                $this->Error('Zlib not available, can\'t handle alpha channel: ' . $file);
            $wpjobportal_data = gzuncompress($wpjobportal_data);
            $wpjobportal_color = '';
            $alpha = '';
            if ($ct == 4) {
                // Gray image
                $len = 2 * $w;
                for ($wpjobportal_i = 0; $wpjobportal_i < $h; $wpjobportal_i++) {
                    $pos = (1 + $len) * $wpjobportal_i;
                    $wpjobportal_color .= $wpjobportal_data[$pos];
                    $alpha .= $wpjobportal_data[$pos];
                    $line = wpjobportalphplib::wpJP_substr($wpjobportal_data, $pos + 1, $len);
                    $wpjobportal_color .= wpjobportalphplib::wpJP_preg_replace('/(.)./s', '$1', $line);
                    $alpha .= wpjobportalphplib::wpJP_preg_replace('/.(.)/s', '$1', $line);
                }
            } else {
                // RGB image
                $len = 4 * $w;
                for ($wpjobportal_i = 0; $wpjobportal_i < $h; $wpjobportal_i++) {
                    $pos = (1 + $len) * $wpjobportal_i;
                    $wpjobportal_color .= $wpjobportal_data[$pos];
                    $alpha .= $wpjobportal_data[$pos];
                    $line = wpjobportalphplib::wpJP_substr($wpjobportal_data, $pos + 1, $len);
                    $wpjobportal_color .= wpjobportalphplib::wpJP_preg_replace('/(.{3})./s', '$1', $line);
                    $alpha .= wpjobportalphplib::wpJP_preg_replace('/.{3}(.)/s', '$1', $line);
                }
            }
            unset($wpjobportal_data);
            $wpjobportal_data = gzcompress($wpjobportal_color);
            $wpjobportal_info['smask'] = gzcompress($alpha);
            if ($this->PDFVersion < '1.4')
                $this->PDFVersion = '1.4';
        }
        $wpjobportal_info['data'] = $wpjobportal_data;
        return $wpjobportal_info;
    }

    function _readstream($f, $wpjobportal_n) {
        // Read n bytes from stream
        $res = '';
        while ($wpjobportal_n > 0 && !feof($f)) {
            $s = fread($f, $wpjobportal_n);
            if ($s === false)
                $this->Error('Error while reading stream');
            $wpjobportal_n -= wpjobportalphplib::wpJP_strlen($s);
            $res .= $s;
        }
        if ($wpjobportal_n > 0)
            $this->Error('Unexpected end of stream');
        return $res;
    }

    function _readint($f) {
        // Read a 4-byte integer from stream
        $a = unpack('Ni', $this->_readstream($f, 4));
        return $a['i'];
    }

    function _parsegif($file) {
        // Extract info from a GIF file (via PNG conversion)
        if (!function_exists('imagepng'))
            $this->Error('GD extension is required for GIF support');
        if (!function_exists('imagecreatefromgif'))
            $this->Error('GD has no GIF read support');
        $wpjobportal_im = imagecreatefromgif($file);
        if (!$wpjobportal_im)
            $this->Error('Missing or incorrect image file: ' . $file);
        imageinterlace($wpjobportal_im, 0);
        $f = @fopen('php://temp', 'rb+');
        if ($f) {
            // Perform conversion in memory
            ob_start();
            imagepng($wpjobportal_im);
            $wpjobportal_data = ob_get_clean();
            imagedestroy($wpjobportal_im);
            fwrite($f, $wpjobportal_data);
            rewind($f);
            $wpjobportal_info = $this->_parsepngstream($f, $file);
            fclose($f);
        } else {
            // Use temporary file
            $tmp = tempnam('.', 'gif');
            if (!$tmp)
                $this->Error('Unable to create a temporary file');
            if (!imagepng($wpjobportal_im, $tmp))
                $this->Error('Error while saving to temporary file');
            imagedestroy($wpjobportal_im);
            $wpjobportal_info = $this->_parsepng($tmp);
            wp_delete_file($tmp);
        }
        return $wpjobportal_info;
    }

    function _newobj() {
        // Begin a new object
        $this->n++;
        $this->offsets[$this->n] = wpjobportalphplib::wpJP_strlen($this->buffer);
        $this->_out($this->n . ' 0 obj');
    }

    function _putstream($s) {
        $this->_out('stream');
        $this->_out($s);
        $this->_out('endstream');
    }

    function _out($s) {
        // Add a line to the document
        if ($this->state == 2)
            $this->pages[$this->page] .= $s . "\n";
        else
            $this->buffer .= $s . "\n";
    }

    function _putpages() {
        $wpjobportal_nb = $this->page;
        if (!empty($this->AliasNbPages)) {
            // Replace number of pages
            for ($wpjobportal_n = 1; $wpjobportal_n <= $wpjobportal_nb; $wpjobportal_n++)
                $this->pages[$wpjobportal_n] = wpjobportalphplib::wpJP_str_replace($this->AliasNbPages, $wpjobportal_nb, $this->pages[$wpjobportal_n]);
        }
        if ($this->DefOrientation == 'P') {
            $wPt = $this->DefPageSize[0] * $this->k;
            $hPt = $this->DefPageSize[1] * $this->k;
        } else {
            $wPt = $this->DefPageSize[1] * $this->k;
            $hPt = $this->DefPageSize[0] * $this->k;
        }
        $filter = ($this->compress) ? '/Filter /FlateDecode ' : '';
        for ($wpjobportal_n = 1; $wpjobportal_n <= $wpjobportal_nb; $wpjobportal_n++) {
            // Page
            $this->_newobj();
            $this->_out('<</Type /Page');
            $this->_out('/Parent 1 0 R');
            if (isset($this->PageSizes[$wpjobportal_n]))
                $this->_out(sprintf('/MediaBox [0 0 %.2F %.2F]', $this->PageSizes[$wpjobportal_n][0], $this->PageSizes[$wpjobportal_n][1]));
            $this->_out('/Resources 2 0 R');
            if (isset($this->PageLinks[$wpjobportal_n])) {
                // Links
                $annots = '/Annots [';
                foreach ($this->PageLinks[$wpjobportal_n] as $pl) {
                    $rect = sprintf('%.2F %.2F %.2F %.2F', $pl[0], $pl[1], $pl[0] + $pl[2], $pl[1] - $pl[3]);
                    $annots .= '<</Type /Annot /Subtype /Link /Rect [' . $rect . '] /Border [0 0 0] ';
                    if (is_string($pl[4]))
                        $annots .= '/A <</S /URI /URI ' . $this->_textstring($pl[4]) . '>>>>';
                    else {
                        $l = $this->links[$pl[4]];
                        $h = isset($this->PageSizes[$l[0]]) ? $this->PageSizes[$l[0]][1] : $hPt;
                        $annots .= sprintf('/Dest [%d 0 R /XYZ 0 %.2F null]>>', 1 + 2 * $l[0], $h - $l[1] * $this->k);
                    }
                }
                $this->_out($annots . ']');
            }
            if ($this->PDFVersion > '1.3')
                $this->_out('/Group <</Type /Group /S /Transparency /CS /DeviceRGB>>');
            $this->_out('/Contents ' . ($this->n + 1) . ' 0 R>>');
            $this->_out('endobj');
            // Page content
            $p = ($this->compress) ? gzcompress($this->pages[$wpjobportal_n]) : $this->pages[$wpjobportal_n];
            $this->_newobj();
            $this->_out('<<' . $filter . '/Length ' . wpjobportalphplib::wpJP_strlen($p) . '>>');
            $this->_putstream($p);
            $this->_out('endobj');
        }
        // Pages root
        $this->offsets[1] = wpjobportalphplib::wpJP_strlen($this->buffer);
        $this->_out('1 0 obj');
        $this->_out('<</Type /Pages');
        $wpjobportal_kids = '/Kids [';
        for ($wpjobportal_i = 0; $wpjobportal_i < $wpjobportal_nb; $wpjobportal_i++)
            $wpjobportal_kids .= (3 + 2 * $wpjobportal_i) . ' 0 R ';
        $this->_out($wpjobportal_kids . ']');
        $this->_out('/Count ' . $wpjobportal_nb);
        $this->_out(sprintf('/MediaBox [0 0 %.2F %.2F]', $wPt, $hPt));
        $this->_out('>>');
        $this->_out('endobj');
    }

    function _putfonts() {
        $wpjobportal_nf = $this->n;
        foreach ($this->diffs as $wpjobportal_diff) {
            // Encodings
            $this->_newobj();
            $this->_out('<</Type /Encoding /BaseEncoding /WinAnsiEncoding /Differences [' . $wpjobportal_diff . ']>>');
            $this->_out('endobj');
        }
        foreach ($this->FontFiles as $file => $wpjobportal_info) {
            // Font file embedding
            $this->_newobj();
            $this->FontFiles[$file]['n'] = $this->n;

            $filestring = wp_remote_get($this->fontpath . $file);
            if (is_wp_error($filestring)) {
                $this->Error('Font file not found: ' . $file);
            }
            // $wpjobportal_font = file_get_contents($this->fontpath . $file, true);
            // if (!$wpjobportal_font)
            //     $this->Error('Font file not found: ' . $file);
            $wpjobportal_compressed = (wpjobportalphplib::wpJP_substr($file, -2) == '.z');
            if (!$wpjobportal_compressed && isset($wpjobportal_info['length2']))
                $wpjobportal_font = wpjobportalphplib::wpJP_substr($wpjobportal_font, 6, $wpjobportal_info['length1']) . wpjobportalphplib::wpJP_substr($wpjobportal_font, 6 + $wpjobportal_info['length1'] + 6, $wpjobportal_info['length2']);
            $this->_out('<</Length ' . wpjobportalphplib::wpJP_strlen($wpjobportal_font));
            if ($wpjobportal_compressed)
                $this->_out('/Filter /FlateDecode');
            $this->_out('/Length1 ' . $wpjobportal_info['length1']);
            if (isset($wpjobportal_info['length2']))
                $this->_out('/Length2 ' . $wpjobportal_info['length2'] . ' /Length3 0');
            $this->_out('>>');
            $this->_putstream($wpjobportal_font);
            $this->_out('endobj');
        }
        foreach ($this->fonts as $wpjobportal_k => $wpjobportal_font) {
            // Font objects
            $this->fonts[$wpjobportal_k]['n'] = $this->n + 1;
            $type = $wpjobportal_font['type'];
            $wpjobportal_name = $wpjobportal_font['name'];
            if ($type == 'Core') {
                // Core font
                $this->_newobj();
                $this->_out('<</Type /Font');
                $this->_out('/BaseFont /' . $wpjobportal_name);
                $this->_out('/Subtype /Type1');
                if ($wpjobportal_name != 'Symbol' && $wpjobportal_name != 'ZapfDingbats')
                    $this->_out('/Encoding /WinAnsiEncoding');
                $this->_out('>>');
                $this->_out('endobj');
            }
            elseif ($type == 'Type1' || $type == 'TrueType') {
                // Additional Type1 or TrueType/OpenType font
                $this->_newobj();
                $this->_out('<</Type /Font');
                $this->_out('/BaseFont /' . $wpjobportal_name);
                $this->_out('/Subtype /' . $type);
                $this->_out('/FirstChar 32 /LastChar 255');
                $this->_out('/Widths ' . ($this->n + 1) . ' 0 R');
                $this->_out('/FontDescriptor ' . ($this->n + 2) . ' 0 R');
                if (isset($wpjobportal_font['diffn']))
                    $this->_out('/Encoding ' . ($wpjobportal_nf + $wpjobportal_font['diffn']) . ' 0 R');
                else
                    $this->_out('/Encoding /WinAnsiEncoding');
                $this->_out('>>');
                $this->_out('endobj');
                // Widths
                $this->_newobj();
                $wpjobportal_cw = &$wpjobportal_font['cw'];
                $s = '[';
                for ($wpjobportal_i = 32; $wpjobportal_i <= 255; $wpjobportal_i++)
                    $s .= $wpjobportal_cw[chr($wpjobportal_i)] . ' ';
                $this->_out($s . ']');
                $this->_out('endobj');
                // Descriptor
                $this->_newobj();
                $s = '<</Type /FontDescriptor /FontName /' . $wpjobportal_name;
                foreach ($wpjobportal_font['desc'] as $wpjobportal_k => $v)
                    $s .= ' /' . $wpjobportal_k . ' ' . $v;
                if (!empty($wpjobportal_font['file']))
                    $s .= ' /FontFile' . ($type == 'Type1' ? '' : '2') . ' ' . $this->FontFiles[$wpjobportal_font['file']]['n'] . ' 0 R';
                $this->_out($s . '>>');
                $this->_out('endobj');
            }
            else {
                // Allow for additional types
                $mtd = '_put' . wpjobportalphplib::wpJP_strtolower($type);
                if (!method_exists($this, $mtd))
                    $this->Error('Unsupported font type: ' . $type);
                $this->$mtd($wpjobportal_font);
            }
        }
    }

    function _putimages() {
        foreach (array_keys($this->images) as $file) {
            $this->_putimage($this->images[$file]);
            unset($this->images[$file]['data']);
            unset($this->images[$file]['smask']);
        }
    }

    function _putimage(&$wpjobportal_info) {
        $this->_newobj();
        $wpjobportal_info['n'] = $this->n;
        $this->_out('<</Type /XObject');
        $this->_out('/Subtype /Image');
        $this->_out('/Width ' . $wpjobportal_info['w']);
        $this->_out('/Height ' . $wpjobportal_info['h']);
        if ($wpjobportal_info['cs'] == 'Indexed')
            $this->_out('/ColorSpace [/Indexed /DeviceRGB ' . (wpjobportalphplib::wpJP_strlen($wpjobportal_info['pal']) / 3 - 1) . ' ' . ($this->n + 1) . ' 0 R]');
        else {
            $this->_out('/ColorSpace /' . $wpjobportal_info['cs']);
            if ($wpjobportal_info['cs'] == 'DeviceCMYK')
                $this->_out('/Decode [1 0 1 0 1 0 1 0]');
        }
        $this->_out('/BitsPerComponent ' . $wpjobportal_info['bpc']);
        if (isset($wpjobportal_info['f']))
            $this->_out('/Filter /' . $wpjobportal_info['f']);
        if (isset($wpjobportal_info['dp']))
            $this->_out('/DecodeParms <<' . $wpjobportal_info['dp'] . '>>');
        if (isset($wpjobportal_info['trns']) && is_array($wpjobportal_info['trns'])) {
            $trns = '';
            for ($wpjobportal_i = 0; $wpjobportal_i < count($wpjobportal_info['trns']); $wpjobportal_i++)
                $trns .= $wpjobportal_info['trns'][$wpjobportal_i] . ' ' . $wpjobportal_info['trns'][$wpjobportal_i] . ' ';
            $this->_out('/Mask [' . $trns . ']');
        }
        if (isset($wpjobportal_info['smask']))
            $this->_out('/SMask ' . ($this->n + 1) . ' 0 R');
        $this->_out('/Length ' . wpjobportalphplib::wpJP_strlen($wpjobportal_info['data']) . '>>');
        $this->_putstream($wpjobportal_info['data']);
        $this->_out('endobj');
        // Soft mask
        if (isset($wpjobportal_info['smask'])) {
            $dp = '/Predictor 15 /Colors 1 /BitsPerComponent 8 /Columns ' . $wpjobportal_info['w'];
            $smask = array('w' => $wpjobportal_info['w'], 'h' => $wpjobportal_info['h'], 'cs' => 'DeviceGray', 'bpc' => 8, 'f' => $wpjobportal_info['f'], 'dp' => $dp, 'data' => $wpjobportal_info['smask']);
            $this->_putimage($smask);
        }
        // Palette
        if ($wpjobportal_info['cs'] == 'Indexed') {
            $filter = ($this->compress) ? '/Filter /FlateDecode ' : '';
            $pal = ($this->compress) ? gzcompress($wpjobportal_info['pal']) : $wpjobportal_info['pal'];
            $this->_newobj();
            $this->_out('<<' . $filter . '/Length ' . wpjobportalphplib::wpJP_strlen($pal) . '>>');
            $this->_putstream($pal);
            $this->_out('endobj');
        }
    }

    function _putxobjectdict() {
        foreach ($this->images as $wpjobportal_image)
            $this->_out('/I' . $wpjobportal_image['i'] . ' ' . $wpjobportal_image['n'] . ' 0 R');
    }

    function _putresourcedict() {
        $this->_out('/ProcSet [/PDF /Text /ImageB /ImageC /ImageI]');
        $this->_out('/Font <<');
        foreach ($this->fonts as $wpjobportal_font)
            $this->_out('/F' . $wpjobportal_font['i'] . ' ' . $wpjobportal_font['n'] . ' 0 R');
        $this->_out('>>');
        $this->_out('/XObject <<');
        $this->_putxobjectdict();
        $this->_out('>>');
    }

    function _putresources() {
        $this->_putfonts();
        $this->_putimages();
        // Resource dictionary
        $this->offsets[2] = wpjobportalphplib::wpJP_strlen($this->buffer);
        $this->_out('2 0 obj');
        $this->_out('<<');
        $this->_putresourcedict();
        $this->_out('>>');
        $this->_out('endobj');
    }

    function _putinfo() {
        $this->_out('/Producer ' . $this->_textstring('FPDF ' . FPDF_VERSION));
        if (!empty($this->title))
            $this->_out('/Title ' . $this->_textstring($this->title));
        if (!empty($this->subject))
            $this->_out('/Subject ' . $this->_textstring($this->subject));
        if (!empty($this->author))
            $this->_out('/Author ' . $this->_textstring($this->author));
        if (!empty($this->keywords))
            $this->_out('/Keywords ' . $this->_textstring($this->keywords));
        if (!empty($this->creator))
            $this->_out('/Creator ' . $this->_textstring($this->creator));
        $this->_out('/CreationDate ' . $this->_textstring('D:' . @gmdate('YmdHis')));
    }

    function _putcatalog() {
        $this->_out('/Type /Catalog');
        $this->_out('/Pages 1 0 R');
        if ($this->ZoomMode == 'fullpage')
            $this->_out('/OpenAction [3 0 R /Fit]');
        elseif ($this->ZoomMode == 'fullwidth')
            $this->_out('/OpenAction [3 0 R /FitH null]');
        elseif ($this->ZoomMode == 'real')
            $this->_out('/OpenAction [3 0 R /XYZ null null 1]');
        elseif (!is_string($this->ZoomMode))
            $this->_out('/OpenAction [3 0 R /XYZ null null ' . sprintf('%.2F', $this->ZoomMode / 100) . ']');
        if ($this->LayoutMode == 'single')
            $this->_out('/PageLayout /SinglePage');
        elseif ($this->LayoutMode == 'continuous')
            $this->_out('/PageLayout /OneColumn');
        elseif ($this->LayoutMode == 'two')
            $this->_out('/PageLayout /TwoColumnLeft');
    }

    function _putheader() {
        $this->_out('%PDF-' . $this->PDFVersion);
    }

    function _puttrailer() {
        $this->_out('/Size ' . ($this->n + 1));
        $this->_out('/Root ' . $this->n . ' 0 R');
        $this->_out('/Info ' . ($this->n - 1) . ' 0 R');
    }

    function _enddoc() {
        $this->_putheader();
        $this->_putpages();
        $this->_putresources();
        // Info
        $this->_newobj();
        $this->_out('<<');
        $this->_putinfo();
        $this->_out('>>');
        $this->_out('endobj');
        // Catalog
        $this->_newobj();
        $this->_out('<<');
        $this->_putcatalog();
        $this->_out('>>');
        $this->_out('endobj');
        // Cross-ref
        $o = wpjobportalphplib::wpJP_strlen($this->buffer);
        $this->_out('xref');
        $this->_out('0 ' . ($this->n + 1));
        $this->_out('0000000000 65535 f ');
        for ($wpjobportal_i = 1; $wpjobportal_i <= $this->n; $wpjobportal_i++)
            $this->_out(sprintf('%010d 00000 n ', $this->offsets[$wpjobportal_i]));
        // Trailer
        $this->_out('trailer');
        $this->_out('<<');
        $this->_puttrailer();
        $this->_out('>>');
        $this->_out('startxref');
        $this->_out($o);
        $this->_out('%%EOF');
        $this->state = 3;
    }

// End of class
}

// Handle special IE contype request
if (isset($_SERVER['HTTP_USER_AGENT']) && $_SERVER['HTTP_USER_AGENT'] == 'contype') {
    header('Content-Type: application/pdf');
    exit;
}
?>
