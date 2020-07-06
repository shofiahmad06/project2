<?php
namespace Dompdf;

use FontLib\Font;


class FontMetrics
{
  
    const CACHE_FILE = "dompdf_font_family_cache.php";

   
    protected $pdf;

  
    protected $canvas;

    
    protected $fontLookup = array();

 
    private $options;

  
    public function __construct(Canvas $canvas, Options $options)
    {
        $this->setCanvas($canvas);
        $this->setOptions($options);
        $this->loadFontFamilies();
    }

  
    public function save_font_families()
    {
        $this->saveFontFamilies();
    }

    
    public function saveFontFamilies()
    {
        // replace the path to the DOMPDF font directories with the corresponding constants (allows for more portability)
        $cacheData = sprintf("<?php return array (%s", PHP_EOL);
        foreach ($this->fontLookup as $family => $variants) {
            $cacheData .= sprintf("  '%s' => array(%s", addslashes($family), PHP_EOL);
            foreach ($variants as $variant => $path) {
                $path = sprintf("'%s'", $path);
                $path = str_replace('\'' . $this->getOptions()->getFontDir() , '$fontDir . \'' , $path);
                $path = str_replace('\'' . $this->getOptions()->getRootDir() , '$rootDir . \'' , $path);
                $cacheData .= sprintf("    '%s' => %s,%s", $variant, $path, PHP_EOL);
            }
            $cacheData .= sprintf("  ),%s", PHP_EOL);
        }
        $cacheData .= ") ?>";
        file_put_contents($this->getCacheFile(), $cacheData);
    }

   
    public function load_font_families()
    {
        $this->loadFontFamilies();
    }

   
    public function loadFontFamilies()
    {
        $fontDir = $this->getOptions()->getFontDir();
        $rootDir = $this->getOptions()->getRootDir();

        // FIXME: tempoarary define constants for cache files <= v0.6.2
        if (!defined("DOMPDF_DIR")) { define("DOMPDF_DIR", $rootDir); }
        if (!defined("DOMPDF_FONT_DIR")) { define("DOMPDF_FONT_DIR", $fontDir); }

        $file = $rootDir . "/lib/fonts/dompdf_font_family_cache.dist.php";
        $distFonts = require $file;

        if (!is_readable($this->getCacheFile())) {
            $this->fontLookup = $distFonts;
            return;
        }

        $cacheData = require $this->getCacheFile();

        $this->fontLookup = array();
        if (is_array($this->fontLookup)) {
            foreach ($cacheData as $key => $value) {
                $this->fontLookup[stripslashes($key)] = $value;
            }
        }

        // Merge provided fonts
        $this->fontLookup += $distFonts;
    }

 
    public function register_font($style, $remote_file, $context = null)
    {
        return $this->registerFont($style, $remote_file);
    }

  
    public function registerFont($style, $remoteFile, $context = null)
    {
        $fontDir = $this->getOptions()->getFontDir();
        $fontname = mb_strtolower($style["family"]);
        $families = $this->getFontFamilies();

        $entry = array();
        if (isset($families[$fontname])) {
            $entry = $families[$fontname];
        }

        $localFile = $fontDir . DIRECTORY_SEPARATOR . md5($remoteFile);
        $localTempFile = $this->options->get('tempDir') . "/" . md5($remoteFile);
        $cacheEntry = $localFile;
        $localFile .= ".".strtolower(pathinfo($remoteFile,PATHINFO_EXTENSION));

        $styleString = $this->getType("{$style['weight']} {$style['style']}");

        if ( !isset($entry[$styleString]) ) {
            $entry[$styleString] = $cacheEntry;

            // Download the remote file
            list($remoteFileContent, $http_response_header) = @Helpers::getFileContent($remoteFile, $context);
            if (false === $remoteFileContent) {
                return false;
            }
            file_put_contents($localTempFile, $remoteFileContent);

            $font = Font::load($localTempFile);

            if (!$font) {
                unlink($localTempFile);
                return false;
            }

            $font->parse();
            $font->saveAdobeFontMetrics("$cacheEntry.ufm");
            $font->close();

            unlink($localTempFile);

            if ( !file_exists("$cacheEntry.ufm") ) {
                return false;
            }

            // Save the changes
            file_put_contents($localFile, $remoteFileContent);

            if ( !file_exists($localFile) ) {
                unlink("$cacheEntry.ufm");
                return false;
            }

            $this->setFontFamily($fontname, $entry);
            $this->saveFontFamilies();
        }

        return true;
    }

 
    public function get_text_width($text, $font, $size, $word_spacing = 0.0, $char_spacing = 0.0)
    {
        //return self::$_pdf->get_text_width($text, $font, $size, $word_spacing, $char_spacing);
        return $this->getTextWidth($text, $font, $size, $word_spacing, $char_spacing);
    }

   loat
     */
    public function getTextWidth($text, $font, $size, $wordSpacing = 0.0, $charSpacing = 0.0)
    {
        // @todo Make sure this cache is efficient before enabling it
        static $cache = array();

        if ($text === "") {
            return 0;
        }

        // Don't cache long strings
        $useCache = !isset($text[50]); // Faster than strlen

        $key = "$font/$size/$wordSpacing/$charSpacing";

        if ($useCache && isset($cache[$key][$text])) {
            return $cache[$key]["$text"];
        }

        $width = $this->getCanvas()->get_text_width($text, $font, $size, $wordSpacing, $charSpacing);

        if ($useCache) {
            $cache[$key][$text] = $width;
        }

        return $width;
    }

   
    public function get_font_height($font, $size)
    {
        return $this->getFontHeight($font, $size);
    }

  
    public function getFontHeight($font, $size)
    {
        return $this->getCanvas()->get_font_height($font, $size);
    }

 
    public function get_font($family_raw, $subtype_raw = "normal")
    {
        return $this->getFont($family_raw, $subtype_raw);
    }

   
    public function getFont($familyRaw, $subtypeRaw = "normal")
    {
        static $cache = array();

        if (isset($cache[$familyRaw][$subtypeRaw])) {
            return $cache[$familyRaw][$subtypeRaw];
        }


        $subtype = strtolower($subtypeRaw);

        if ($familyRaw) {
            $family = str_replace(array("'", '"'), "", strtolower($familyRaw));

            if (isset($this->fontLookup[$family][$subtype])) {
                return $cache[$familyRaw][$subtypeRaw] = $this->fontLookup[$family][$subtype];
            }

            return null;
        }

        $family = "serif";

        if (isset($this->fontLookup[$family][$subtype])) {
            return $cache[$familyRaw][$subtypeRaw] = $this->fontLookup[$family][$subtype];
        }

        if (!isset($this->fontLookup[$family])) {
            return null;
        }

        $family = $this->fontLookup[$family];

        foreach ($family as $sub => $font) {
            if (strpos($subtype, $sub) !== false) {
                return $cache[$familyRaw][$subtypeRaw] = $font;
            }
        }

        if ($subtype !== "normal") {
            foreach ($family as $sub => $font) {
                if ($sub !== "normal") {
                    return $cache[$familyRaw][$subtypeRaw] = $font;
                }
            }
        }

        $subtype = "normal";

        if (isset($family[$subtype])) {
            return $cache[$familyRaw][$subtypeRaw] = $family[$subtype];
        }

        return null;
    }

   
    public function get_family($family)
    {
        return $this->getFamily($family);
    }

 
    public function getFamily($family)
    {
        $family = str_replace(array("'", '"'), "", mb_strtolower($family));

        if (isset($this->fontLookup[$family])) {
            return $this->fontLookup[$family];
        }

        return null;
    }

 
    public function get_type($type)
    {
        return $this->getType($type);
    }

  
    public function getType($type)
    {
        if (preg_match("/bold/i", $type)) {
            if (preg_match("/italic|oblique/i", $type)) {
                $type = "bold_italic";
            } else {
                $type = "bold";
            }
        } elseif (preg_match("/italic|oblique/i", $type)) {
            $type = "italic";
        } else {
            $type = "normal";
        }

        return $type;
    }

  deprecated
   
    public function get_font_families()
    {
        return $this->getFontFamilies();
    }

  
    public function getFontFamilies()
    {
        return $this->fontLookup;
    }

   
    public function set_font_family($fontname, $entry)
    {
        $this->setFontFamily($fontname, $entry);
    }

   
    public function setFontFamily($fontname, $entry)
    {
        $this->fontLookup[mb_strtolower($fontname)] = $entry;
    }

    
    public function getCacheFile()
    {
        return $this->getOptions()->getFontDir() . DIRECTORY_SEPARATOR . self::CACHE_FILE;
    }

    
    public function setOptions(Options $options)
    {
        $this->options = $options;
        return $this;
    }

   
    public function getOptions()
    {
        return $this->options;
    }

   
    public function setCanvas(Canvas $canvas)
    {
        $this->canvas = $canvas;
        // Still write deprecated pdf for now. It might be used by a parent class.
        $this->pdf = $canvas;
        return $this;
    }

   
    public function getCanvas()
    {
        return $this->canvas;
    }
}
