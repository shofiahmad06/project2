<?php

namespace Dompdf\Css;

use DOMXPath;
use Dompdf\Dompdf;
use Dompdf\Helpers;
use Dompdf\Exception;
use Dompdf\FontMetrics;
use Dompdf\Frame\FrameTree;


class Stylesheet
{
   
    const DEFAULT_STYLESHEET = "/lib/res/html.css";

  
    const ORIG_UA = 1;

 
    const ORIG_USER = 2;

 
    const ORIG_AUTHOR = 3;


    private static $_stylesheet_origins = array(
        self::ORIG_UA => 0x00000000, // user agent declarations
        self::ORIG_USER => 0x10000000, // user normal declarations
        self::ORIG_AUTHOR => 0x30000000, // author normal declarations
    );

  
    const SPEC_NON_CSS = 0x20000000;

    private $_dompdf;

  
    private $_styles;


    private $_protocol;

    private $_base_host;

 
    private $_base_path;

  
    private $_page_styles;

   
    private $_loaded_files;

    private $_current_origin = self::ORIG_UA;


    static $ACCEPTED_DEFAULT_MEDIA_TYPE = "print";
    static $ACCEPTED_GENERIC_MEDIA_TYPES = array("all", "static", "visual", "bitmap", "paged", "dompdf");
    static $VALID_MEDIA_TYPES = array("all", "aural", "bitmap", "braille", "dompdf", "embossed", "handheld", "paged", "print", "projection", "screen", "speech", "static", "tty", "tv", "visual");

    /**
     * @var FontMetrics
     */
    private $fontMetrics;


    function __construct(Dompdf $dompdf)
    {
        $this->_dompdf = $dompdf;
        $this->setFontMetrics($dompdf->getFontMetrics());
        $this->_styles = array();
        $this->_loaded_files = array();
        list($this->_protocol, $this->_base_host, $this->_base_path) = Helpers::explode_url($_SERVER["SCRIPT_FILENAME"]);
        $this->_page_styles = array("base" => null);
    }

   
    function set_protocol($protocol)
    {
        $this->_protocol = $protocol;
    }

  on set_host($host)
    {
        $this->_base_host = $host;
    }

    
    function set_base_path($path)
    {
        $this->_base_path = $path;
    }

    /**
     * Return the Dompdf object
     *
     * @return Dompdf
     */
    function get_dompdf()
    {
        return $this->_dompdf;
    }

    /**
     * Return the base protocol for this stylesheet
     *
     * @return string
     */
    function get_protocol()
    {
        return $this->_protocol;
    }

    /**
     * Return the base host for this stylesheet
     *
     * @return string
     */
    function get_host()
    {
        return $this->_base_host;
    }

    /**
     * Return the base path for this stylesheet
     *
     * @return string
     */
    function get_base_path()
    {
        return $this->_base_path;
    }

    /**
     * Return the array of page styles
     *
     * @return Style[]
     */
    function get_page_styles()
    {
        return $this->_page_styles;
    }


    function add_style($key, Style $style)
    {
        if (!is_string($key)) {
            throw new Exception("CSS rule must be keyed by a string.");
        }

        if (!isset($this->_styles[$key])) {
            $this->_styles[$key] = array();
        }
        $new_style = clone $style;
        $new_style->set_origin($this->_current_origin);
        $this->_styles[$key][] = $new_style;
    }

  
    function lookup($key)
    {
        if (!isset($this->_styles[$key])) {
            return null;
        }

        return $this->_styles[$key];
    }

    function create_style(Style $parent = null)
    {
        return new Style($this, $this->_current_origin);
    }

    function load_css(&$css, $origin = self::ORIG_AUTHOR)
    {
        if ($origin) {
            $this->_current_origin = $origin;
        }
        $this->_parse_css($css);
    }



    function load_css_file($file, $origin = self::ORIG_AUTHOR)
    {
        if ($origin) {
            $this->_current_origin = $origin;
        }

        // Prevent circular references
        if (isset($this->_loaded_files[$file])) {
            return;
        }

        $this->_loaded_files[$file] = true;

        if (strpos($file, "data:") === 0) {
            $parsed = Helpers::parse_data_uri($file);
            $css = $parsed["data"];
        } else {
            $parsed_url = Helpers::explode_url($file);

            list($this->_protocol, $this->_base_host, $this->_base_path, $filename) = $parsed_url;

            // Fix submitted by Nick Oostveen for aliased directory support:
            if ($this->_protocol == "") {
                $file = $this->_base_path . $filename;
            } else {
                $file = Helpers::build_url($this->_protocol, $this->_base_host, $this->_base_path, $filename);
            }

            list($css, $http_response_header) = Helpers::getFileContent($file, $this->_dompdf->getHttpContext());

            $good_mime_type = true;

            // See http://the-stickman.com/web-development/php/getting-http-response-headers-when-using-file_get_contents/
            if (isset($http_response_header) && !$this->_dompdf->getQuirksmode()) {
                foreach ($http_response_header as $_header) {
                    if (preg_match("@Content-Type:\s*([\w/]+)@i", $_header, $matches) &&
                        ($matches[1] !== "text/css")
                    ) {
                        $good_mime_type = false;
                    }
                }
            }

            if (!$good_mime_type || $css == "") {
                Helpers::record_warnings(E_USER_WARNING, "Unable to load css file $file", __FILE__, __LINE__);
                return;
            }
        }

        $this->_parse_css($css);
    }


    private function _specificity($selector, $origin = self::ORIG_AUTHOR)
    {
      

        $a = ($selector === "!attr") ? 1 : 0;

        $b = min(mb_substr_count($selector, "#"), 255);

        $c = min(mb_substr_count($selector, ".") +
            mb_substr_count($selector, "["), 255);

        $d = min(mb_substr_count($selector, " ") +
            mb_substr_count($selector, ">") +
            mb_substr_count($selector, "+"), 255);

     

        if (!in_array($selector[0], array(" ", ">", ".", "#", "+", ":", "[")) && $selector !== "*") {
            $d++;
        }

        if ($this->_dompdf->getOptions()->getDebugCss()) {
            /*DEBUGCSS*/
            print "<pre>\n";
            /*DEBUGCSS*/
            printf("_specificity(): 0x%08x \"%s\"\n", self::$_stylesheet_origins[$origin] + (($a << 24) | ($b << 16) | ($c << 8) | ($d)), $selector);
            /*DEBUGCSS*/
            print "</pre>";
        }

        return self::$_stylesheet_origins[$origin] + (($a << 24) | ($b << 16) | ($c << 8) | ($d));
    }

    private function _css_selector_to_xpath($selector, $first_pass = false)
    {



        $query = "//";

        $pseudo_elements = array();

        $pseudo_classes = array();

        

        $delimiters = array(" ", ">", ".", "#", "+", ":", "[", "(");

      
        if ($selector[0] === "[") {
            $selector = "*$selector";
        }

        
        if (!in_array($selector[0], $delimiters)) {
            $selector = " $selector";
        }

        $tok = "";
        $len = mb_strlen($selector);
        $i = 0;

        while ($i < $len) {

            $s = $selector[$i];
            $i++;

            // Eat characters up to the next delimiter
            $tok = "";
            $in_attr = false;
            $in_func = false;

            while ($i < $len) {
                $c = $selector[$i];
                $c_prev = $selector[$i - 1];

                if (!$in_func && !$in_attr && in_array($c, $delimiters) && !(($c == $c_prev) == ":")) {
                    break;
                }

                if ($c_prev === "[") {
                    $in_attr = true;
                }
                if ($c_prev === "(") {
                    $in_func = true;
                }

                $tok .= $selector[$i++];

                if ($in_attr && $c === "]") {
                    $in_attr = false;
                    break;
                }
                if ($in_func && $c === ")") {
                    $in_func = false;
                    break;
                }
            }

            switch ($s) {

                case " ":
                case ">":
                    // All elements matching the next token that are direct children of
                    // the current token
                    $expr = $s === " " ? "descendant" : "child";

                    if (mb_substr($query, -1, 1) !== "/") {
                        $query .= "/";
                    }

                    // Tag names are case-insensitive
                    $tok = strtolower($tok);

                    if (!$tok) {
                        $tok = "*";
                    }

                    $query .= "$expr::$tok";
                    $tok = "";
                    break;

                case ".":
                case "#":
                    // All elements matching the current token with a class/id equal to
                    // the _next_ token.

                    $attr = $s === "." ? "class" : "id";

                    // empty class/id == *
                    if (mb_substr($query, -1, 1) === "/") {
                        $query .= "*";
                    }

                  
                    $query .= "[contains(concat(' ', @$attr, ' '), concat(' ', '$tok', ' '))]";
                    $tok = "";
                    break;

                case "+":
                    // All sibling elements that folow the current token
                    if (mb_substr($query, -1, 1) !== "/") {
                        $query .= "/";
                    }

                    $query .= "following-sibling::$tok";
                    $tok = "";
                    break;

                case ":":
                    $i2 = $i - strlen($tok) - 2; // the char before ":"
                    if (($i2 < 0 || !isset($selector[$i2]) || (in_array($selector[$i2], $delimiters) && $selector[$i2] != ":")) && substr($query, -1) != "*") {
                        $query .= "*";
                    }

                    $last = false;

                    // Pseudo-classes
                    switch ($tok) {

                        case "first-child":
                            $query .= "[1]";
                            $tok = "";
                            break;

                        case "last-child":
                            $query .= "[not(following-sibling::*)]";
                            $tok = "";
                            break;

                        case "first-of-type":
                            $query .= "[position() = 1]";
                            $tok = "";
                            break;

                        case "last-of-type":
                            $query .= "[position() = last()]";
                            $tok = "";
                            break;

                        // an+b, n, odd, and even
                        /** @noinspection PhpMissingBreakStatementInspection */
                        case "nth-last-of-type":
                            $last = true;
                        case "nth-of-type":
                            //FIXME: this fix-up is pretty ugly, would parsing the selector in reverse work better generally?
                            $descendant_delimeter = strrpos($query, "::");
                            $isChild = substr($query, $descendant_delimeter-5, 5) == "child";
                            $el = substr($query, $descendant_delimeter+2);
                            $query = substr($query, 0, strrpos($query, "/")) . ($isChild ? "/" : "//") . $el;

                            $pseudo_classes[$tok] = true;
                            $p = $i + 1;
                            $nth = trim(mb_substr($selector, $p, strpos($selector, ")", $i) - $p));

                            // 1
                            if (preg_match("/^\d+$/", $nth)) {
                                $condition = "position() = $nth";
                            } // odd
                            elseif ($nth === "odd") {
                                $condition = "(position() mod 2) = 1";
                            } // even
                            elseif ($nth === "even") {
                                $condition = "(position() mod 2) = 0";
                            } // an+b
                            else {
                                $condition = $this->_selector_an_plus_b($nth, $last);
                            }

                            $query .= "[$condition]";
                            $tok = "";
                            break;
                        /** @noinspection PhpMissingBreakStatementInspection */
                        case "nth-last-child":
                            $last = true;
                        case "nth-child":
                            //FIXME: this fix-up is pretty ugly, would parsing the selector in reverse work better generally?
                            $descendant_delimeter = strrpos($query, "::");
                            $isChild = substr($query, $descendant_delimeter-5, 5) == "child";
                            $el = substr($query, $descendant_delimeter+2);
                            $query = substr($query, 0, strrpos($query, "/")) . ($isChild ? "/" : "//") . "*";

                            $pseudo_classes[$tok] = true;
                            $p = $i + 1;
                            $nth = trim(mb_substr($selector, $p, strpos($selector, ")", $i) - $p));

                            // 1
                            if (preg_match("/^\d+$/", $nth)) {
                                $condition = "position() = $nth";
                            } // odd
                            elseif ($nth === "odd") {
                                $condition = "(position() mod 2) = 1";
                            } // even
                            elseif ($nth === "even") {
                                $condition = "(position() mod 2) = 0";
                            } // an+b
                            else {
                                $condition = $this->_selector_an_plus_b($nth, $last);
                            }

                            $query .= "[$condition]";
                            if ($el != "*") {
                                $query .= "[name() = '$el']";
                            }
                            $tok = "";
                            break;

                        //TODO: bit of a hack attempt at matches support, currently only matches against elements
                        case "matches":
                            $pseudo_classes[$tok] = true;
                            $p = $i + 1;
                            $matchList = trim(mb_substr($selector, $p, strpos($selector, ")", $i) - $p));

                            // Tag names are case-insensitive
                            $elements = array_map("trim", explode(",", strtolower($matchList)));
                            foreach ($elements as &$element) {
                                $element = "name() = '$element'";
                            }

                            $query .= "[" . implode(" or ", $elements) . "]";
                            $tok = "";
                            break;

                        case "link":
                            $query .= "[@href]";
                            $tok = "";
                            break;

                        case "first-line":
                        case ":first-line":
                        case "first-letter":
                        case ":first-letter":
                            // TODO
                            $el = trim($tok, ":");
                            $pseudo_elements[$el] = true;
                            break;

                            // N/A
                        case "focus":
                        case "active":
                        case "hover":
                        case "visited":
                            $query .= "[false()]";
                            $tok = "";
                            break;

                        /* Pseudo-elements */
                        case "before":
                        case ":before":
                        case "after":
                        case ":after":
                            $pos = trim($tok, ":");
                            $pseudo_elements[$pos] = true;
                            if (!$first_pass) {
                                $query .= "/*[@$pos]";
                            }

                            $tok = "";
                            break;

                        case "empty":
                            $query .= "[not(*) and not(normalize-space())]";
                            $tok = "";
                            break;

                        case "disabled":
                        case "checked":
                            $query .= "[@$tok]";
                            $tok = "";
                            break;

                        case "enabled":
                            $query .= "[not(@disabled)]";
                            $tok = "";
                            break;
                    }

                    break;

                case "[":
                    // Attribute selectors.  All with an attribute matching the following token(s)
                    $attr_delimiters = array("=", "]", "~", "|", "$", "^", "*");
                    $tok_len = mb_strlen($tok);
                    $j = 0;

                    $attr = "";
                    $op = "";
                    $value = "";

                    while ($j < $tok_len) {
                        if (in_array($tok[$j], $attr_delimiters)) {
                            break;
                        }
                        $attr .= $tok[$j++];
                    }

                    switch ($tok[$j]) {

                        case "~":
                        case "|":
                        case "$":
                        case "^":
                        case "*":
                            $op .= $tok[$j++];

                            if ($tok[$j] !== "=") {
                                throw new Exception("Invalid CSS selector syntax: invalid attribute selector: $selector");
                            }

                            $op .= $tok[$j];
                            break;

                        case "=":
                            $op = "=";
                            break;

                    }

                    // Read the attribute value, if required
                    if ($op != "") {
                        $j++;
                        while ($j < $tok_len) {
                            if ($tok[$j] === "]") {
                                break;
                            }
                            $value .= $tok[$j++];
                        }
                    }

                    if ($attr == "") {
                        throw new Exception("Invalid CSS selector syntax: missing attribute name");
                    }

                    $value = trim($value, "\"'");

                    switch ($op) {

                        case "":
                            $query .= "[@$attr]";
                            break;

                        case "=":
                            $query .= "[@$attr=\"$value\"]";
                            break;

                        case "~=":
                            // FIXME: this will break if $value contains quoted strings
                            // (e.g. [type~="a b c" "d e f"])
                            $values = explode(" ", $value);
                            $query .= "[";

                            foreach ($values as $val) {
                                $query .= "@$attr=\"$val\" or ";
                            }

                            $query = rtrim($query, " or ") . "]";
                            break;

                        case "|=":
                            $values = explode("-", $value);
                            $query .= "[";

                            foreach ($values as $val) {
                                $query .= "starts-with(@$attr, \"$val\") or ";
                            }

                            $query = rtrim($query, " or ") . "]";
                            break;

                        case "$=":
                            $query .= "[substring(@$attr, string-length(@$attr)-" . (strlen($value) - 1) . ")=\"$value\"]";
                            break;

                        case "^=":
                            $query .= "[starts-with(@$attr,\"$value\")]";
                            break;

                        case "*=":
                            $query .= "[contains(@$attr,\"$value\")]";
                            break;
                    }

                    break;
            }
        }
        $i++;



        // Trim the trailing '/' from the query
        if (mb_strlen($query) > 2) {
            $query = rtrim($query, "/");
        }

        return array("query" => $query, "pseudo_elements" => $pseudo_elements);
    }

 
    protected function _selector_an_plus_b($expr, $last = false)
    {
        $expr = preg_replace("/\s/", "", $expr);
        if (!preg_match("/^(?P<a>-?[0-9]*)?n(?P<b>[-+]?[0-9]+)?$/", $expr, $matches)) {
            return "false()";
        }

        $a = ((isset($matches["a"]) && $matches["a"] !== "") ? intval($matches["a"]) : 1);
        $b = ((isset($matches["b"]) && $matches["b"] !== "") ? intval($matches["b"]) : 0);

        $position = ($last ? "(last()-position()+1)" : "position()");

        if ($b == 0) {
            return "($position mod $a) = 0";
        } else {
            $compare = (($a < 0) ? "<=" : ">=");
            $b2 = -$b;
            if ($b2 >= 0) {
                $b2 = "+$b2";
            }
            return "($position $compare $b) and ((($position $b2) mod " . abs($a) . ") = 0)";
        }
    }

   
    function apply_styles(FrameTree $tree)
    {
       

        $styles = array();
        $xp = new DOMXPath($tree->get_dom());
        $DEBUGCSS = $this->_dompdf->getOptions()->getDebugCss();

        // Add generated content
        foreach ($this->_styles as $selector => $selector_styles) {
            /** @var Style $style */
            foreach ($selector_styles as $style) {
                if (strpos($selector, ":before") === false && strpos($selector, ":after") === false) {
                    continue;
                }

                $query = $this->_css_selector_to_xpath($selector, true);

                // Retrieve the nodes, limit to body for generated content
                //TODO: If we use a context node can we remove the leading dot?
                $nodes = @$xp->query('.' . $query["query"]);
                if ($nodes == null) {
                    Helpers::record_warnings(E_USER_WARNING, "The CSS selector '$selector' is not valid", __FILE__, __LINE__);
                    continue;
                }

                /** @var \DOMElement $node */
                foreach ($nodes as $node) {
                    foreach (array_keys($query["pseudo_elements"], true, true) as $pos) {
                        // Do not add a new pseudo element if another one already matched
                        if ($node->hasAttribute("dompdf_{$pos}_frame_id")) {
                            continue;
                        }

                        if (($src = $this->_image($style->get_prop('content'))) !== "none") {
                            $new_node = $node->ownerDocument->createElement("img_generated");
                            $new_node->setAttribute("src", $src);
                        } else {
                            $new_node = $node->ownerDocument->createElement("dompdf_generated");
                        }

                        $new_node->setAttribute($pos, $pos);
                        $new_frame_id = $tree->insert_node($node, $new_node, $pos);
                        $node->setAttribute("dompdf_{$pos}_frame_id", $new_frame_id);
                    }
                }
            }
        }

        // Apply all styles in stylesheet
        foreach ($this->_styles as $selector => $selector_styles) {
            /** @var Style $style */
            foreach ($selector_styles as $style) {
                $query = $this->_css_selector_to_xpath($selector);

                // Retrieve the nodes
                $nodes = @$xp->query($query["query"]);
                if ($nodes == null) {
                    Helpers::record_warnings(E_USER_WARNING, "The CSS selector '$selector' is not valid", __FILE__, __LINE__);
                    continue;
                }

                $spec = $this->_specificity($selector, $style->get_origin());

                foreach ($nodes as $node) {
                    // Retrieve the node id
                    // Only DOMElements get styles
                    if ($node->nodeType != XML_ELEMENT_NODE) {
                        continue;
                    }

                    $id = $node->getAttribute("frame_id");

                    // Assign the current style to the scratch array
                    $styles[$id][$spec][] = $style;
                }
            }
        }

        // Set the page width, height, and orientation based on the canvas paper size
        $canvas = $this->_dompdf->getCanvas();
        $paper_width = $canvas->get_width();
        $paper_height = $canvas->get_height();
        $paper_orientation = ($paper_width > $paper_height ? "landscape" : "portrait");

        // Now create the styles and assign them to the appropriate frames. (We
        // iterate over the tree using an implicit FrameTree iterator.)
        $root_flg = false;
        foreach ($tree->get_frames() as $frame) {
            // Helpers::pre_r($frame->get_node()->nodeName . ":");
            if (!$root_flg && $this->_page_styles["base"]) {
                $style = $this->_page_styles["base"];
                $root_flg = true;

                // set the page width, height, and orientation based on the base page style
                if ($style->size !== "auto") {
                    list($paper_width, $paper_height) = $style->size;
                }
                $paper_width = $paper_width - (float)$style->length_in_pt($style->margin_left) - (float)$style->length_in_pt($style->margin_right);
                $paper_height = $paper_height - (float)$style->length_in_pt($style->margin_top) - (float)$style->length_in_pt($style->margin_bottom);
                $paper_orientation = ($paper_width > $paper_height ? "landscape" : "portrait");
            } else {
                $style = $this->create_style();
            }

            // Find nearest DOMElement parent
            $p = $frame;
            while ($p = $p->get_parent()) {
                if ($p->get_node()->nodeType == XML_ELEMENT_NODE) {
                    break;
                }
            }

            // Styles can only be applied directly to DOMElements; anonymous
            // frames inherit from their parent
            if ($frame->get_node()->nodeType != XML_ELEMENT_NODE) {
                if ($p) {
                    $style->inherit($p->get_style());
                }

                $frame->set_style($style);
                continue;
            }

            $id = $frame->get_id();

            // Handle HTML 4.0 attributes
            AttributeTranslator::translate_attributes($frame);
            if (($str = $frame->get_node()->getAttribute(AttributeTranslator::$_style_attr)) !== "") {
                $styles[$id][self::SPEC_NON_CSS][] = $this->_parse_properties($str);
            }

            // Locate any additional style attributes
            if (($str = $frame->get_node()->getAttribute("style")) !== "") {
                // Destroy CSS comments
                $str = preg_replace("'/\*.*?\*/'si", "", $str);

                $spec = $this->_specificity("!attr", self::ORIG_AUTHOR);
                $styles[$id][$spec][] = $this->_parse_properties($str);
            }

            // Grab the applicable styles
            if (isset($styles[$id])) {

                /** @var array[][] $applied_styles */
                $applied_styles = $styles[$frame->get_id()];

                // Sort by specificity
                ksort($applied_styles);

                if ($DEBUGCSS) {
                    $debug_nodename = $frame->get_node()->nodeName;
                    print "<pre>\n[$debug_nodename\n";
                    foreach ($applied_styles as $spec => $arr) {
                        printf("specificity: 0x%08x\n", $spec);
                        /** @var Style $s */
                        foreach ($arr as $s) {
                            print "[\n";
                            $s->debug_print();
                            print "]\n";
                        }
                    }
                }

                // Merge the new styles with the inherited styles
                $acceptedmedia = self::$ACCEPTED_GENERIC_MEDIA_TYPES;
                $acceptedmedia[] = $this->_dompdf->getOptions()->getDefaultMediaType();
                foreach ($applied_styles as $arr) {
                    /** @var Style $s */
                    foreach ($arr as $s) {
                        $media_queries = $s->get_media_queries();
                        foreach ($media_queries as $media_query) {
                            list($media_query_feature, $media_query_value) = $media_query;
                            // if any of the Style's media queries fail then do not apply the style
                            //TODO: When the media query logic is fully developed we should not apply the Style when any of the media queries fail or are bad, per https://www.w3.org/TR/css3-mediaqueries/#error-handling
                            if (in_array($media_query_feature, self::$VALID_MEDIA_TYPES)) {
                                if ((strlen($media_query_value) === 0 && !in_array($media_query, $acceptedmedia)) || (in_array($media_query, $acceptedmedia) && $media_query_value == "not")) {
                                    continue (3);
                                }
                            } else {
                                switch ($media_query_feature) {
                                    case "height":
                                        if ($paper_height !== (float)$style->length_in_pt($media_query_value)) {
                                            continue (3);
                                        }
                                        break;
                                    case "min-height":
                                        if ($paper_height < (float)$style->length_in_pt($media_query_value)) {
                                            continue (3);
                                        }
                                        break;
                                    case "max-height":
                                        if ($paper_height > (float)$style->length_in_pt($media_query_value)) {
                                            continue (3);
                                        }
                                        break;
                                    case "width":
                                        if ($paper_width !== (float)$style->length_in_pt($media_query_value)) {
                                            continue (3);
                                        }
                                        break;
                                    case "min-width":
                                        //if (min($paper_width, $media_query_width) === $paper_width) {
                                        if ($paper_width < (float)$style->length_in_pt($media_query_value)) {
                                            continue (3);
                                        }
                                        break;
                                    case "max-width":
                                        //if (max($paper_width, $media_query_width) === $paper_width) {
                                        if ($paper_width > (float)$style->length_in_pt($media_query_value)) {
                                            continue (3);
                                        }
                                        break;
                                    case "orientation":
                                        if ($paper_orientation !== $media_query_value) {
                                            continue (3);
                                        }
                                        break;
                                    default:
                                        Helpers::record_warnings(E_USER_WARNING, "Unknown media query: $media_query_feature", __FILE__, __LINE__);
                                        break;
                                }
                            }
                        }

                        $style->merge($s);
                    }
                }
            }

            // Inherit parent's styles if required
            if ($p) {

                if ($DEBUGCSS) {
                    print "inherit:\n";
                    print "[\n";
                    $p->get_style()->debug_print();
                    print "]\n";
                }

                $style->inherit($p->get_style());
            }

            if ($DEBUGCSS) {
                print "DomElementStyle:\n";
                print "[\n";
                $style->debug_print();
                print "]\n";
                print "/$debug_nodename]\n</pre>";
            }

            /*DEBUGCSS print: see below different print debugging method
            Helpers::pre_r($frame->get_node()->nodeName . ":");
            echo "<pre>";
            echo $style;
            echo "</pre>";*/
            $frame->set_style($style);

        }

        // We're done!  Clean out the registry of all styles since we
        // won't be needing this later.
        foreach (array_keys($this->_styles) as $key) {
            $this->_styles[$key] = null;
            unset($this->_styles[$key]);
        }

    }

    /**
     * parse a CSS string using a regex parser
     * Called by {@link Stylesheet::parse_css()}
     *
     * @param string $str
     *
     * @throws Exception
     */
    private function _parse_css($str)
    {

        $str = trim($str);

        // Destroy comments and remove HTML comments
        $css = preg_replace(array(
            "'/\*.*?\*/'si",
            "/^<!--/",
            "/-->$/"
        ), "", $str);

    

        if (preg_match_all($re, $css, $matches, PREG_SET_ORDER) === false) {
            // An error occurred
            throw new Exception("Error parsing css file: preg_match_all() failed.");
        }

     

        $media_query_regex = "/(?:((only|not)?\s*(" . implode("|", self::$VALID_MEDIA_TYPES) . "))|(\s*\(\s*((?:(min|max)-)?([\w\-]+))\s*(?:\:\s*(.*?)\s*)?\)))/isx";

        //Helpers::pre_r($matches);
        foreach ($matches as $match) {
            $match[2] = trim($match[2]);

            if ($match[2] !== "") {
                // Handle @rules
                switch ($match[2]) {

                    case "import":
                        $this->_parse_import($match[3]);
                        break;

                    case "media":
                        $acceptedmedia = self::$ACCEPTED_GENERIC_MEDIA_TYPES;
                        $acceptedmedia[] = $this->_dompdf->getOptions()->getDefaultMediaType();

                        $media_queries = preg_split("/\s*,\s*/", mb_strtolower(trim($match[3])));
                        foreach ($media_queries as $media_query) {
                            if (in_array($media_query, $acceptedmedia)) {
                                //if we have a media type match go ahead and parse the stylesheet
                                $this->_parse_sections($match[5]);
                                break;
                            } elseif (!in_array($media_query, self::$VALID_MEDIA_TYPES)) {
                                // otherwise conditionally parse the stylesheet assuming there are parseable media queries
                                if (preg_match_all($media_query_regex, $media_query, $media_query_matches, PREG_SET_ORDER) !== false) {
                                    $mq = array();
                                    foreach ($media_query_matches as $media_query_match) {
                                        if (empty($media_query_match[1]) === false) {
                                            $media_query_feature = strtolower($media_query_match[3]);
                                            $media_query_value = strtolower($media_query_match[2]);
                                            $mq[] = array($media_query_feature, $media_query_value);
                                        } else if (empty($media_query_match[4]) === false) {
                                            $media_query_feature = strtolower($media_query_match[5]);
                                            $media_query_value = (array_key_exists(8, $media_query_match) ? strtolower($media_query_match[8]) : null);
                                            $mq[] = array($media_query_feature, $media_query_value);
                                        }
                                    }
                                    $this->_parse_sections($match[5], $mq);
                                    break;
                                }
                            }
                        }
                        break;

                    case "page":
                       
                        $page_selector = trim($match[3]);

                        $key = null;
                        switch ($page_selector) {
                            case "":
                                $key = "base";
                                break;

                            case ":left":
                            case ":right":
                            case ":odd":
                            case ":even":
                            /** @noinspection PhpMissingBreakStatementInspection */
                            case ":first":
                                $key = $page_selector;

                            default:
                                continue;
                        }

                        // Store the style for later...
                        if (empty($this->_page_styles[$key])) {
                            $this->_page_styles[$key] = $this->_parse_properties($match[5]);
                        } else {
                            $this->_page_styles[$key]->merge($this->_parse_properties($match[5]));
                        }
                        break;

                    case "font-face":
                        $this->_parse_font_face($match[5]);
                        break;

                    default:
                        // ignore everything else
                        break;
                }

                continue;
            }

            if ($match[7] !== "") {
                $this->_parse_sections($match[7]);
            }

        }
    }

    protected function _image($val)
    {
        $DEBUGCSS = $this->_dompdf->getOptions()->getDebugCss();
        $parsed_url = "none";

        if (mb_strpos($val, "url") === false) {
            $path = "none"; //Don't resolve no image -> otherwise would prefix path and no longer recognize as none
        } else {
            $val = preg_replace("/url\(\s*['\"]?([^'\")]+)['\"]?\s*\)/", "\\1", trim($val));

            // Resolve the url now in the context of the current stylesheet
            $parsed_url = Helpers::explode_url($val);
            if ($parsed_url["protocol"] == "" && $this->get_protocol() == "") {
                if ($parsed_url["path"][0] === '/' || $parsed_url["path"][0] === '\\') {
                    $path = $_SERVER["DOCUMENT_ROOT"] . '/';
                } else {
                    $path = $this->get_base_path();
                }

                $path .= $parsed_url["path"] . $parsed_url["file"];
                $path = realpath($path);
                // If realpath returns FALSE then specifically state that there is no background image
                // FIXME: Is this causing problems for imported CSS files? There are some './none' references when running the test cases.
                if (!$path) {
                    $path = 'none';
                }
            } else {
                $path = Helpers::build_url($this->get_protocol(),
                    $this->get_host(),
                    $this->get_base_path(),
                    $val);
            }
        }

        if ($DEBUGCSS) {
            print "<pre>[_image\n";
            print_r($parsed_url);
            print $this->get_protocol() . "\n" . $this->get_base_path() . "\n" . $path . "\n";
            print "_image]</pre>";;
        }

        return $path;
    }

    /**
     * parse @import{} sections
     *
     * @param string $url the url of the imported CSS file
     */
    private function _parse_import($url)
    {
        $arr = preg_split("/[\s\n,]/", $url, -1, PREG_SPLIT_NO_EMPTY);
        $url = array_shift($arr);
        $accept = false;

        if (count($arr) > 0) {
            $acceptedmedia = self::$ACCEPTED_GENERIC_MEDIA_TYPES;
            $acceptedmedia[] = $this->_dompdf->getOptions()->getDefaultMediaType();

            // @import url media_type [media_type...]
            foreach ($arr as $type) {
                if (in_array(mb_strtolower(trim($type)), $acceptedmedia)) {
                    $accept = true;
                    break;
                }
            }

        } else {
            // unconditional import
            $accept = true;
        }

        if ($accept) {
            // Store our current base url properties in case the new url is elsewhere
            $protocol = $this->_protocol;
            $host = $this->_base_host;
            $path = $this->_base_path;

            // $url = str_replace(array('"',"url", "(", ")"), "", $url);
            // If the protocol is php, assume that we will import using file://
            // $url = Helpers::build_url($protocol == "php://" ? "file://" : $protocol, $host, $path, $url);
            // Above does not work for subfolders and absolute urls.
            // Todo: As above, do we need to replace php or file to an empty protocol for local files?

            $url = $this->_image($url);

            $this->load_css_file($url);

            // Restore the current base url
            $this->_protocol = $protocol;
            $this->_base_host = $host;
            $this->_base_path = $path;
        }

    }

    /**
     * parse @font-face{} sections
     * http://www.w3.org/TR/css3-fonts/#the-font-face-rule
     *
     * @param string $str CSS @font-face rules
     */
    private function _parse_font_face($str)
    {
        $descriptors = $this->_parse_properties($str);

        preg_match_all("/(url|local)\s*\([\"\']?([^\"\'\)]+)[\"\']?\)\s*(format\s*\([\"\']?([^\"\'\)]+)[\"\']?\))?/i", $descriptors->src, $src);

        $sources = array();
        $valid_sources = array();

        foreach ($src[0] as $i => $value) {
            $source = array(
                "local" => strtolower($src[1][$i]) === "local",
                "uri" => $src[2][$i],
                "format" => $src[4][$i],
                "path" => Helpers::build_url($this->_protocol, $this->_base_host, $this->_base_path, $src[2][$i]),
            );

            if (!$source["local"] && in_array($source["format"], array("", "truetype"))) {
                $valid_sources[] = $source;
            }

            $sources[] = $source;
        }

        // No valid sources
        if (empty($valid_sources)) {
            return;
        }

        $style = array(
            "family" => $descriptors->get_font_family_raw(),
            "weight" => $descriptors->font_weight,
            "style" => $descriptors->font_style,
        );

        $this->getFontMetrics()->registerFont($style, $valid_sources[0]["path"], $this->_dompdf->getHttpContext());
    }

    /**
     * parse regular CSS blocks
     *
     * _parse_properties() creates a new Style object based on the provided
     * CSS rules.
     *
     * @param string $str CSS rules
     * @return Style
     */
    private function _parse_properties($str)
    {
        $properties = preg_split("/;(?=(?:[^\(]*\([^\)]*\))*(?![^\)]*\)))/", $str);
        $DEBUGCSS = $this->_dompdf->getOptions()->getDebugCss();

        if ($DEBUGCSS) print '[_parse_properties';

        // Create the style
        $style = new Style($this, Stylesheet::ORIG_AUTHOR);

        foreach ($properties as $prop) {
           
            if ($DEBUGCSS) print '(';

            $important = false;
            $prop = trim($prop);

            if (substr($prop, -9) === 'important') {
                $prop_tmp = rtrim(substr($prop, 0, -9));

                if (substr($prop_tmp, -1) === '!') {
                    $prop = rtrim(substr($prop_tmp, 0, -1));
                    $important = true;
                }
            }

            if ($prop === "") {
                if ($DEBUGCSS) print 'empty)';
                continue;
            }

            $i = mb_strpos($prop, ":");
            if ($i === false) {
                if ($DEBUGCSS) print 'novalue' . $prop . ')';
                continue;
            }

            $prop_name = rtrim(mb_strtolower(mb_substr($prop, 0, $i)));
            $value = ltrim(mb_substr($prop, $i + 1));
            if ($DEBUGCSS) print $prop_name . ':=' . $value . ($important ? '!IMPORTANT' : '') . ')';
          
            if ($important) {
                $style->important_set($prop_name);
            }
            //For easier debugging, don't use overloading of assignments with __set
            $style->$prop_name = $value;
            //$style->props_set($prop_name, $value);
        }
        if ($DEBUGCSS) print '_parse_properties]';

        return $style;
    }

    /**
     * parse selector + rulesets
     *
     * @param string $str CSS selectors and rulesets
     * @param array $media_queries
     */
    private function _parse_sections($str, $media_queries = array())
    {
        // Pre-process: collapse all whitespace and strip whitespace around '>',
        // '.', ':', '+', '#'

        $patterns = array("/[\\s\n]+/", "/\\s+([>.:+#])\\s+/");
        $replacements = array(" ", "\\1");
        $str = preg_replace($patterns, $replacements, $str);
        $DEBUGCSS = $this->_dompdf->getOptions()->getDebugCss();

        $sections = explode("}", $str);
        if ($DEBUGCSS) print '[_parse_sections';
        foreach ($sections as $sect) {
            $i = mb_strpos($sect, "{");
            if ($i === false) { continue; }

            //$selectors = explode(",", mb_substr($sect, 0, $i));
            $selectors = preg_split("/,(?![^\(]*\))/", mb_substr($sect, 0, $i),0, PREG_SPLIT_NO_EMPTY);
            if ($DEBUGCSS) print '[section';
            
            $style = $this->_parse_properties(trim(mb_substr($sect, $i + 1)));

            // Assign it to the selected elements
            foreach ($selectors as $selector) {
                $selector = trim($selector);

                if ($selector == "") {
                    if ($DEBUGCSS) print '#empty#';
                    continue;
                }
                if ($DEBUGCSS) print '#' . $selector . '#';
                //if ($DEBUGCSS) { if (strpos($selector,'p') !== false) print '!!!p!!!#'; }

                //FIXME: tag the selector with a hash of the media query to separate it from non-conditional styles (?), xpath comments are probably not what we want to do here
                if (count($media_queries) > 0) {
                    $style->set_media_queries($media_queries);
                }
                $this->add_style($selector, $style);
            }

            if ($DEBUGCSS) print 'section]';
        }

        if ($DEBUGCSS) print '_parse_sections]';
    }

    /**
     * @return string
     */
    public static function getDefaultStylesheet()
    {
        $dir = realpath(__DIR__ . "/../..");
        return $dir . self::DEFAULT_STYLESHEET;
    }

    /**
     * @param FontMetrics $fontMetrics
     * @return $this
     */
    public function setFontMetrics(FontMetrics $fontMetrics)
    {
        $this->fontMetrics = $fontMetrics;
        return $this;
    }

    /**
     * @return FontMetrics
     */
    public function getFontMetrics()
    {
        return $this->fontMetrics;
    }

    function __toString()
    {
        $str = "";
        foreach ($this->_styles as $selector => $selector_styles) {
            /** @var Style $style */
            foreach ($selector_styles as $style) {
                $str .= "$selector => " . $style->__toString() . "\n";
            }
        }

        return $str;
    }
}
