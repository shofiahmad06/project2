<?php

namespace Dompdf;

use DOMDocument;
use DOMNode;
use Dompdf\Adapter\CPDF;
use DOMXPath;
use Dompdf\Frame\Factory;
use Dompdf\Frame\FrameTree;
use HTML5_Tokenizer;
use HTML5_TreeBuilder;
use Dompdf\Image\Cache;
use Dompdf\Renderer\ListBullet;
use Dompdf\Css\Stylesheet;


class Dompdf
{
 
    private $version = 'dompdf';

   
    private $dom;

  
    private $tree;

   
    private $css;

   
    private $canvas;


    private $paperSize;

   
    private $paperOrientation = "portrait";

  
    private $callbacks = array();

   
    private $cacheId;

 
    private $baseHost = "";

 
    private $basePath = "";

   
    private $protocol;

    private $httpContext;

   
    private $startTime = null;

   
    private $systemLocale = null;

   
    private $localeStandard = false;

 
    private $defaultView = "Fit";

   
    private $defaultViewOptions = array();

  
    private $quirksmode = false;

   
    private $allowedProtocols = array(null, "", "file://", "http://", "https://");

 
    private $allowedLocalFileExtensions = array("htm", "html");

   
    private $messages = array();

   
    private $options;

   
    private $fontMetrics;

   eprecated
     */
    public static $native_fonts = array(
        "courier", "courier-bold", "courier-oblique", "courier-boldoblique",
        "helvetica", "helvetica-bold", "helvetica-oblique", "helvetica-boldoblique",
        "times-roman", "times-bold", "times-italic", "times-bolditalic",
        "symbol", "zapfdinbats"
    );

  
    public static $nativeFonts = array(
        "courier", "courier-bold", "courier-oblique", "courier-boldoblique",
        "helvetica", "helvetica-bold", "helvetica-oblique", "helvetica-boldoblique",
        "times-roman", "times-bold", "times-italic", "times-bolditalic",
        "symbol", "zapfdinbats"
    );

   
    public function __construct($options = null)
    {
        mb_internal_encoding('UTF-8');

        if (isset($options) && $options instanceof Options) {
            $this->setOptions($options);
        } elseif (is_array($options)) {
            $this->setOptions(new Options($options));
        } else {
            $this->setOptions(new Options());
        }

        $versionFile = realpath(__DIR__ . '/../VERSION');
        if (file_exists($versionFile) && ($version = file_get_contents($versionFile)) !== false && $version !== '$Format:<%h>$') {
          $this->version = sprintf('dompdf %s', $version);
        }

        $this->localeStandard = sprintf('%.1f', 1.0) == '1.0';
        $this->saveLocale();
        $this->paperSize = $this->options->getDefaultPaperSize();
        $this->paperOrientation = $this->options->getDefaultPaperOrientation();

        $this->setCanvas(CanvasFactory::get_instance($this, $this->paperSize, $this->paperOrientation));
        $this->setFontMetrics(new FontMetrics($this->getCanvas(), $this->getOptions()));
        $this->css = new Stylesheet($this);

        $this->restoreLocale();
    }

 
    private function saveLocale()
    {
        if ($this->localeStandard) {
            return;
        }

        $this->systemLocale = setlocale(LC_NUMERIC, "0");
        setlocale(LC_NUMERIC, "C");
    }

  
    private function restoreLocale()
    {
        if ($this->localeStandard) {
            return;
        }

        setlocale(LC_NUMERIC, $this->systemLocale);
    }

  
    public function load_html_file($file)
    {
        $this->loadHtmlFile($file);
    }

   
    public function loadHtmlFile($file)
    {
        $this->saveLocale();

        if (!$this->protocol && !$this->baseHost && !$this->basePath) {
            list($this->protocol, $this->baseHost, $this->basePath) = Helpers::explode_url($file);
        }
        $protocol = strtolower($this->protocol);

        if ( !in_array($protocol, $this->allowedProtocols) ) {
            throw new Exception("Permission denied on $file. The communication protocol is not supported.");
        }

        if (!$this->options->isRemoteEnabled() && ($protocol != "" && $protocol !== "file://")) {
            throw new Exception("Remote file requested, but remote file download is disabled.");
        }

        if ($protocol == "" || $protocol === "file://") {
            $realfile = realpath($file);

            $chroot = realpath($this->options->getChroot());
            if ($chroot && strpos($realfile, $chroot) !== 0) {
                throw new Exception("Permission denied on $file. The file could not be found under the directory specified by Options::chroot.");
            }

            $ext = strtolower(pathinfo($realfile, PATHINFO_EXTENSION));
            if (!in_array($ext, $this->allowedLocalFileExtensions)) {
                throw new Exception("Permission denied on $file.");
            }

            if (!$realfile) {
                throw new Exception("File '$file' not found.");
            }

            $file = $realfile;
        }

        list($contents, $http_response_header) = Helpers::getFileContent($file, $this->httpContext);
        $encoding = 'UTF-8';

        // See http://the-stickman.com/web-development/php/getting-http-response-headers-when-using-file_get_contents/
        if (isset($http_response_header)) {
            foreach ($http_response_header as $_header) {
                if (preg_match("@Content-Type:\s*[\w/]+;\s*?charset=([^\s]+)@i", $_header, $matches)) {
                    $encoding = strtoupper($matches[1]);
                    break;
                }
            }
        }

        $this->restoreLocale();

        $this->loadHtml($contents, $encoding);
    }

   @deprecated
     */
    public function load_html($str, $encoding = 'UTF-8')
    {
        $this->loadHtml($str, $encoding);
    }

   
    public function loadHtml($str, $encoding = 'UTF-8')
    {
        $this->saveLocale();

        
        $known_encodings = mb_list_encodings();
        mb_detect_order('auto');
        if (($file_encoding = mb_detect_encoding($str, null, true)) === false) {
            $file_encoding = "auto";
        }
        if (in_array(strtoupper($file_encoding), array('UTF-8','UTF8')) === false) {
            $str = mb_convert_encoding($str, 'UTF-8', $file_encoding);
        }

        $metatags = array(
            '@<meta\s+http-equiv="Content-Type"\s+content="(?:[\w/]+)(?:;\s*?charset=([^\s"]+))?@i',
            '@<meta\s+content="(?:[\w/]+)(?:;\s*?charset=([^\s"]+))"?\s+http-equiv="Content-Type"@i',
            '@<meta [^>]*charset\s*=\s*["\']?\s*([^"\' ]+)@i',
        );
        foreach ($metatags as $metatag) {
            if (preg_match($metatag, $str, $matches)) {
                if (isset($matches[1]) && in_array($matches[1], $known_encodings)) {
                    $document_encoding = $matches[1];
                    break;
                }
            }
        }
        if (isset($document_encoding) && in_array(strtoupper($document_encoding), array('UTF-8','UTF8')) === false) {
            $str = preg_replace('/charset=([^\s"]+)/i', 'charset=UTF-8', $str);
        } elseif (isset($document_encoding) === false && strpos($str, '<head>') !== false) {
            $str = str_replace('<head>', '<head><meta http-equiv="Content-Type" content="text/html;charset=UTF-8">', $str);
        } elseif (isset($document_encoding) === false) {
            $str = '<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">' . $str;
        }

       
        if (substr($str, 0, 3) == chr(0xEF) . chr(0xBB) . chr(0xBF)) {
            $str = substr($str, 3);
        }

        // Store parsing warnings as messages
        set_error_handler(array("\\Dompdf\\Helpers", "record_warnings"));

        
        $quirksmode = false;

        if ($this->options->isHtml5ParserEnabled()) {
            $tokenizer = new HTML5_Tokenizer($str);
            $tokenizer->parse();
            $doc = $tokenizer->save();

            // Remove #text children nodes in nodes that shouldn't have
            $tag_names = array("html", "table", "tbody", "thead", "tfoot", "tr");
            foreach ($tag_names as $tag_name) {
                $nodes = $doc->getElementsByTagName($tag_name);

                foreach ($nodes as $node) {
                    self::removeTextNodes($node);
                }
            }

            $quirksmode = ($tokenizer->getTree()->getQuirksMode() > HTML5_TreeBuilder::NO_QUIRKS);
        } else {
            
            $doc = new DOMDocument("1.0", $encoding);
            $doc->preserveWhiteSpace = true;
            $doc->loadHTML($str);
            $doc->encoding = $encoding;

            // If some text is before the doctype, we are in quirksmode
            if (preg_match("/^(.+)<!doctype/i", ltrim($str), $matches)) {
                $quirksmode = true;
            } // If no doctype is provided, we are in quirksmode
            elseif (!preg_match("/^<!doctype/i", ltrim($str), $matches)) {
                $quirksmode = true;
            } else {
                // HTML5 <!DOCTYPE html>
                if (!$doc->doctype->publicId && !$doc->doctype->systemId) {
                    $quirksmode = false;
                }

                // not XHTML
                if (!preg_match("/xhtml/i", $doc->doctype->publicId)) {
                    $quirksmode = true;
                }
            }
        }

        $this->dom = $doc;
        $this->quirksmode = $quirksmode;

        $this->tree = new FrameTree($this->dom);

        restore_error_handler();

        $this->restoreLocale();
    }

    
    public static function remove_text_nodes(DOMNode $node)
    {
        self::removeTextNodes($node);
    }

   
    public static function removeTextNodes(DOMNode $node)
    {
        $children = array();
        for ($i = 0; $i < $node->childNodes->length; $i++) {
            $child = $node->childNodes->item($i);
            if ($child->nodeName === "#text") {
                $children[] = $child;
            }
        }

        foreach ($children as $child) {
            $node->removeChild($child);
        }
    }

  
    private function processHtml()
    {
        $this->tree->build_tree();

        $this->css->load_css_file(Stylesheet::getDefaultStylesheet(), Stylesheet::ORIG_UA);

        $acceptedmedia = Stylesheet::$ACCEPTED_GENERIC_MEDIA_TYPES;
        $acceptedmedia[] = $this->options->getDefaultMediaType();

       
        $base_nodes = $this->dom->getElementsByTagName("base");
        if ($base_nodes->length && ($href = $base_nodes->item(0)->getAttribute("href"))) {
            list($this->protocol, $this->baseHost, $this->basePath) = Helpers::explode_url($href);
        }

        
        $this->css->set_protocol($this->protocol);
        $this->css->set_host($this->baseHost);
        $this->css->set_base_path($this->basePath);

        
        $xpath = new DOMXPath($this->dom);
        $stylesheets = $xpath->query("//*[name() = 'link' or name() = 'style']");

        
        foreach ($stylesheets as $tag) {
            switch (strtolower($tag->nodeName)) {
              
                case "link":
                    if (mb_strtolower(stripos($tag->getAttribute("rel"), "stylesheet") !== false) || 
                        mb_strtolower($tag->getAttribute("type")) === "text/css"
                    ) {
                      
                        $formedialist = preg_split("/[\s\n,]/", $tag->getAttribute("media"), -1, PREG_SPLIT_NO_EMPTY);
                        if (count($formedialist) > 0) {
                            $accept = false;
                            foreach ($formedialist as $type) {
                                if (in_array(mb_strtolower(trim($type)), $acceptedmedia)) {
                                    $accept = true;
                                    break;
                                }
                            }

                            if (!$accept) {
                              
                                continue;
                            }
                        }

                        $url = $tag->getAttribute("href");
                        $url = Helpers::build_url($this->protocol, $this->baseHost, $this->basePath, $url);

                        $this->css->load_css_file($url, Stylesheet::ORIG_AUTHOR);
                    }
                    break;

               
                case "style":
                 
                    if ($tag->hasAttributes() &&
                        ($media = $tag->getAttribute("media")) &&
                        !in_array($media, $acceptedmedia)
                    ) {
                        continue;
                    }

                    $css = "";
                    if ($tag->hasChildNodes()) {
                        $child = $tag->firstChild;
                        while ($child) {
                            $css .= $child->nodeValue; // Handle <style><!-- blah --></style>
                            $child = $child->nextSibling;
                        }
                    } else {
                        $css = $tag->nodeValue;
                    }

                    $this->css->load_css($css, Stylesheet::ORIG_AUTHOR);
                    break;
            }
        }
    }

 
    public function enable_caching($cacheId)
    {
        $this->enableCaching($cacheId);
    }

  
    public function enableCaching($cacheId)
    {
        $this->cacheId = $cacheId;
    }

  
    public function parse_default_view($value)
    {
        return $this->parseDefaultView($value);
    }

   
    public function parseDefaultView($value)
    {
        $valid = array("XYZ", "Fit", "FitH", "FitV", "FitR", "FitB", "FitBH", "FitBV");

        $options = preg_split("/\s*,\s*/", trim($value));
        $defaultView = array_shift($options);

        if (!in_array($defaultView, $valid)) {
            return false;
        }

        $this->setDefaultView($defaultView, $options);
        return true;
    }

    /**
     * Renders the HTML to PDF
     */
    public function render()
    {
        $this->saveLocale();
        $options = $this->options;

        $logOutputFile = $options->getLogOutputFile();
        if ($logOutputFile) {
            if (!file_exists($logOutputFile) && is_writable(dirname($logOutputFile))) {
                touch($logOutputFile);
            }

            $this->startTime = microtime(true);
            if (is_writable($logOutputFile)) {
                ob_start();
            }
        }

        $this->processHtml();

        $this->css->apply_styles($this->tree);

        // @page style rules : size, margins
        $pageStyles = $this->css->get_page_styles();
        $basePageStyle = $pageStyles["base"];
        unset($pageStyles["base"]);

        foreach ($pageStyles as $pageStyle) {
            $pageStyle->inherit($basePageStyle);
        }

        $defaultOptionPaperSize = $this->getPaperSize($options->getDefaultPaperSize());
        // If there is a CSS defined paper size compare to the paper size used to create the canvas to determine a
        // recreation need
        if (is_array($basePageStyle->size)) {
            $basePageStyleSize = $basePageStyle->size;
            $this->setPaper(array(0, 0, $basePageStyleSize[0], $basePageStyleSize[1]));
        }

        $paperSize = $this->getPaperSize();
        if (
            $defaultOptionPaperSize[2] !== $paperSize[2] ||
            $defaultOptionPaperSize[3] !== $paperSize[3] ||
            $options->getDefaultPaperOrientation() !== $this->paperOrientation
        ) {
            $this->setCanvas(CanvasFactory::get_instance($this, $this->paperSize, $this->paperOrientation));
            $this->fontMetrics->setCanvas($this->getCanvas());
        }

        $canvas = $this->getCanvas();

        if ($options->isFontSubsettingEnabled() && $canvas instanceof CPDF) {
            foreach ($this->tree->get_frames() as $frame) {
                $style = $frame->get_style();
                $node = $frame->get_node();

                // Handle text nodes
                if ($node->nodeName === "#text") {
                    $chars = mb_strtoupper($node->nodeValue) . mb_strtolower($node->nodeValue);
                    $canvas->register_string_subset($style->font_family, $chars);
                    continue;
                }

               
                if ($style->display === "list-item") {
                    $chars = ListBullet::get_counter_chars($style->list_style_type);
                    $canvas->register_string_subset($style->font_family, $chars);
                    continue;
                }

               
                if ($frame->get_node()->nodeName == "dompdf_generated") {
                    
                    $chars = ListBullet::get_counter_chars('decimal');
                    $canvas->register_string_subset($style->font_family, $chars);
                    $chars = ListBullet::get_counter_chars('upper-alpha');
                    $canvas->register_string_subset($style->font_family, $chars);
                    $chars = ListBullet::get_counter_chars('lower-alpha');
                    $canvas->register_string_subset($style->font_family, $chars);
                    $chars = ListBullet::get_counter_chars('lower-greek');
                    $canvas->register_string_subset($style->font_family, $chars);

                    
                    $decoded_string = preg_replace_callback("/\\\\([0-9a-fA-F]{0,6})/",
                        function ($matches) { return \Dompdf\Helpers::unichr(hexdec($matches[1])); },
                        $style->content);
                    $chars = mb_strtoupper($style->content) . mb_strtolower($style->content) . mb_strtoupper($decoded_string) . mb_strtolower($decoded_string);
                    $canvas->register_string_subset($style->font_family, $chars);
                    continue;
                }
            }
        }

        $root = null;

        foreach ($this->tree->get_frames() as $frame) {
            // Set up the root frame
            if (is_null($root)) {
                $root = Factory::decorate_root($this->tree->get_root(), $this);
                continue;
            }

        
            Factory::decorate_frame($frame, $this, $root);
        }

        // Add meta information
        $title = $this->dom->getElementsByTagName("title");
        if ($title->length) {
            $canvas->add_info("Title", trim($title->item(0)->nodeValue));
        }

        $metas = $this->dom->getElementsByTagName("meta");
        $labels = array(
            "author" => "Author",
            "keywords" => "Keywords",
            "description" => "Subject",
        );
        
        foreach ($metas as $meta) {
            $name = mb_strtolower($meta->getAttribute("name"));
            $value = trim($meta->getAttribute("content"));

            if (isset($labels[$name])) {
                $canvas->add_info($labels[$name], $value);
                continue;
            }

            if ($name === "dompdf.view" && $this->parseDefaultView($value)) {
                $canvas->set_default_view($this->defaultView, $this->defaultViewOptions);
            }
        }

        $root->set_containing_block(0, 0,$canvas->get_width(), $canvas->get_height());
        $root->set_renderer(new Renderer($this));

       
        $root->reflow();

      
        Cache::clear();

        global $_dompdf_warnings, $_dompdf_show_warnings;
        if ($_dompdf_show_warnings && isset($_dompdf_warnings)) {
            echo '<b>Dompdf Warnings</b><br><pre>';
            foreach ($_dompdf_warnings as $msg) {
                echo $msg . "\n";
            }

            if ($canvas instanceof CPDF) {
                echo $canvas->get_cpdf()->messages;
            }
            echo '</pre>';
            flush();
        }

        if ($logOutputFile && is_writable($logOutputFile)) {
            $this->write_log();
            ob_end_clean();
        }

        $this->restoreLocale();
    }

  
    public function add_info($label, $value)
    {
        $canvas = $this->getCanvas();
        if (!is_null($canvas)) {
            $canvas->add_info($label, $value);
        }
    }

   
    private function write_log()
    {
        $log_output_file = $this->getOptions()->getLogOutputFile();
        if (!$log_output_file || !is_writable($log_output_file)) {
            return;
        }

        $frames = Frame::$ID_COUNTER;
        $memory = memory_get_peak_usage(true) / 1024;
        $time = (microtime(true) - $this->startTime) * 1000;

        $out = sprintf(
            "<span style='color: #000' title='Frames'>%6d</span>" .
            "<span style='color: #009' title='Memory'>%10.2f KB</span>" .
            "<span style='color: #900' title='Time'>%10.2f ms</span>" .
            "<span  title='Quirksmode'>  " .
            ($this->quirksmode ? "<span style='color: #d00'> ON</span>" : "<span style='color: #0d0'>OFF</span>") .
            "</span><br />", $frames, $memory, $time);

        $out .= ob_get_contents();
        ob_clean();

        file_put_contents($log_output_file, $out);
    }

   
    public function stream($filename = 'document.pdf', $options = null)
    {
        $this->saveLocale();

        $canvas = $this->getCanvas();
        if (!is_null($canvas)) {
            $canvas->stream($filename, $options);
        }

        $this->restoreLocale();
    }

    
    public function output($options = null)
    {
        $this->saveLocale();

        $canvas = $this->getCanvas();
        if (is_null($canvas)) {
            return null;
        }

        $output = $canvas->output($options);

        $this->restoreLocale();

        return $output;
    }

  
    public function output_html()
    {
        return $this->outputHtml();
    }

    
    public function outputHtml()
    {
        return $this->dom->saveHTML();
    }

  
    public function get_option($key)
    {
        return $this->options->get($key);
    }

    
    public function set_option($key, $value)
    {
        $this->options->set($key, $value);
        return $this;
    }

  
    public function set_options(array $options)
    {
        $this->options->set($options);
        return $this;
    }

   
    public function set_paper($size, $orientation = "portrait")
    {
        $this->setPaper($size, $orientation);
    }

   
    public function setPaper($size, $orientation = "portrait")
    {
        $this->paperSize = $size;
        $this->paperOrientation = $orientation;
        return $this;
    }

   
    public function getPaperSize($paperSize = null)
    {
        $size = $paperSize !== null ? $paperSize : $this->paperSize;
        if (is_array($size)) {
            return $size;
        } else if (isset(Adapter\CPDF::$PAPER_SIZES[mb_strtolower($size)])) {
            return Adapter\CPDF::$PAPER_SIZES[mb_strtolower($size)];
        } else {
            return Adapter\CPDF::$PAPER_SIZES["letter"];
        }
    }

 
    public function getPaperOrientation()
    {
        return $this->paperOrientation;
    }

   
    public function setTree(FrameTree $tree)
    {
        $this->tree = $tree;
        return $this;
    }

  
    public function get_tree()
    {
        return $this->getTree();
    }

    
    public function getTree()
    {
        return $this->tree;
    }

  
    public function set_protocol($protocol)
    {
        return $this->setProtocol($protocol);
    }

   
    public function setProtocol($protocol)
    {
        $this->protocol = $protocol;
        return $this;
    }

 
    public function get_protocol()
    {
        return $this->getProtocol();
    }

   
    public function getProtocol()
    {
        return $this->protocol;
    }

  
    public function set_host($host)
    {
        $this->setBaseHost($host);
    }

   
    public function setBaseHost($baseHost)
    {
        $this->baseHost = $baseHost;
        return $this;
    }

 
    public function get_host()
    {
        return $this->getBaseHost();
    }

 
    public function getBaseHost()
    {
        return $this->baseHost;
    }

  deprecated
     */
    public function set_base_path($path)
    {
        $this->setBasePath($path);
    }

   urn $this
     */
    public function setBasePath($basePath)
    {
        $this->basePath = $basePath;
        return $this;
    }

    
    public function get_base_path()
    {
        return $this->getBasePath();
    }

    
    public function getBasePath()
    {
        return $this->basePath;
    }

   
    public function set_default_view($default_view, $options)
    {
        return $this->setDefaultView($default_view, $options);
    }

  
    public function setDefaultView($defaultView, $options)
    {
        $this->defaultView = $defaultView;
        $this->defaultViewOptions = $options;
        return $this;
    }

   
    public function set_http_context($http_context)
    {
        return $this->setHttpContext($http_context);
    }

    
    public function setHttpContext($httpContext)
    {
        $this->httpContext = $httpContext;
        return $this;
    }

   
    public function get_http_context()
    {
        return $this->getHttpContext();
    }

   
    public function getHttpContext()
    {
        return $this->httpContext;
    }

  
    public function setCanvas(Canvas $canvas)
    {
        $this->canvas = $canvas;
        return $this;
    }

  
    public function get_canvas()
    {
        return $this->getCanvas();
    }

    
    public function getCanvas()
    {
        return $this->canvas;
    }

  
    public function setCss(Stylesheet $css)
    {
        $this->css = $css;
        return $this;
    }

   
    public function get_css()
    {
        return $this->getCss();
    }

   
    public function getCss()
    {
        return $this->css;
    }

   
    public function setDom(DOMDocument $dom)
    {
        $this->dom = $dom;
        return $this;
    }

  
    public function get_dom()
    {
        return $this->getDom();
    }

    
    public function getDom()
    {
        return $this->dom;
    }

    
    public function setOptions(Options $options)
    {
        $this->options = $options;
        $fontMetrics = $this->getFontMetrics();
        if (isset($fontMetrics)) {
            $fontMetrics->setOptions($options);
        }
        return $this;
    }

  
    public function getOptions()
    {
        return $this->options;
    }

   
    public function get_callbacks()
    {
        return $this->getCallbacks();
    }

   
    public function getCallbacks()
    {
        return $this->callbacks;
    }

   
    public function set_callbacks($callbacks)
    {
        $this->setCallbacks($callbacks);
    }

   
    public function setCallbacks($callbacks)
    {
        if (is_array($callbacks)) {
            $this->callbacks = array();
            foreach ($callbacks as $c) {
                if (is_array($c) && isset($c['event']) && isset($c['f'])) {
                    $event = $c['event'];
                    $f = $c['f'];
                    if (is_callable($f) && is_string($event)) {
                        $this->callbacks[$event][] = $f;
                    }
                }
            }
        }
    }

  
    public function get_quirksmode()
    {
        return $this->getQuirksmode();
    }

    
    public function getQuirksmode()
    {
        return $this->quirksmode;
    }

    
    public function setFontMetrics(FontMetrics $fontMetrics)
    {
        $this->fontMetrics = $fontMetrics;
        return $this;
    }

   
    public function getFontMetrics()
    {
        return $this->fontMetrics;
    }

   
    function __get($prop)
    {
        switch ($prop)
        {
            case 'version' :
                return $this->version;
            default:
                throw new Exception( 'Invalid property: ' . $prop );
        }
    }
}
