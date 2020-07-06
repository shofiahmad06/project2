<?php
namespace Dompdf;

class Options
{
   
    private $rootDir;

    
    private $tempDir;

   
    private $fontDir;

   
    private $fontCache;

    
    private $chroot;

   
    private $logOutputFile;

    
    private $defaultMediaType = "screen";

   
    private $defaultPaperSize = "letter";

  
    private $defaultPaperOrientation = "portrait";

   ate $defaultFont = "serif";

  
    private $dpi = 96;

 
    private $fontHeightRatio = 1.1;

   
    private $isPhpEnabled = false;

  
    private $isRemoteEnabled = false;

 
    private $isJavascriptEnabled = true;


    private $isHtml5ParserEnabled = false;

 
    private $isFontSubsettingEnabled = false;

   
    private $debugPng = false;


    private $debugKeepTemp = false;

    private $debugCss = false;


    private $debugLayout = false;

  
    private $debugLayoutLines = true;

  
    private $debugLayoutBlocks = true;


    private $debugLayoutInline = true;

    private $debugLayoutPaddingBox = true;


    private $pdfBackend = "CPDF";


    private $pdflibLicense = "";

  
    private $adminUsername = "user";


    private $adminPassword = "password";


    public function __construct(array $attributes = null)
    {
        $this->setChroot(realpath(__DIR__ . "/../"));
        $this->setRootDir($this->getChroot());
        $this->setTempDir(sys_get_temp_dir());
        $this->setFontDir($this->chroot . DIRECTORY_SEPARATOR . "lib" . DIRECTORY_SEPARATOR . "fonts");
        $this->setFontCache($this->getFontDir());
        $this->setLogOutputFile($this->getTempDir() . DIRECTORY_SEPARATOR . "log.htm");

        if (null !== $attributes) {
            $this->set($attributes);
        }
    }

ic function set($attributes, $value = null)
    {
        if (!is_array($attributes)) {
            $attributes = array($attributes => $value);
        }
        foreach ($attributes as $key => $value) {
            if ($key === 'tempDir' || $key === 'temp_dir') {
                $this->setTempDir($value);
            } elseif ($key === 'fontDir' || $key === 'font_dir') {
                $this->setFontDir($value);
            } elseif ($key === 'fontCache' || $key === 'font_cache') {
                $this->setFontCache($value);
            } elseif ($key === 'chroot') {
                $this->setChroot($value);
            } elseif ($key === 'logOutputFile' || $key === 'log_output_file') {
                $this->setLogOutputFile($value);
            } elseif ($key === 'defaultMediaType' || $key === 'default_media_type') {
                $this->setDefaultMediaType($value);
            } elseif ($key === 'defaultPaperSize' || $key === 'default_paper_size') {
                $this->setDefaultPaperSize($value);
            } elseif ($key === 'defaultPaperOrientation' || $key === 'default_paper_orientation') {
                $this->setDefaultPaperOrientation($value);
            } elseif ($key === 'defaultFont' || $key === 'default_font') {
                $this->setDefaultFont($value);
            } elseif ($key === 'dpi') {
                $this->setDpi($value);
            } elseif ($key === 'fontHeightRatio' || $key === 'font_height_ratio') {
                $this->setFontHeightRatio($value);
            } elseif ($key === 'isPhpEnabled' || $key === 'is_php_enabled' || $key === 'enable_php') {
                $this->setIsPhpEnabled($value);
            } elseif ($key === 'isRemoteEnabled' || $key === 'is_remote_enabled' || $key === 'enable_remote') {
                $this->setIsRemoteEnabled($value);
            } elseif ($key === 'isJavascriptEnabled' || $key === 'is_javascript_enabled' || $key === 'enable_javascript') {
                $this->setIsJavascriptEnabled($value);
            } elseif ($key === 'isHtml5ParserEnabled' || $key === 'is_html5_parser_enabled' || $key === 'enable_html5_parser') {
                $this->setIsHtml5ParserEnabled($value);
            } elseif ($key === 'isFontSubsettingEnabled' || $key === 'is_font_subsetting_enabled' || $key === 'enable_font_subsetting') {
                $this->setIsFontSubsettingEnabled($value);
            } elseif ($key === 'debugPng' || $key === 'debug_png') {
                $this->setDebugPng($value);
            } elseif ($key === 'debugKeepTemp' || $key === 'debug_keep_temp') {
                $this->setDebugKeepTemp($value);
            } elseif ($key === 'debugCss' || $key === 'debug_css') {
                $this->setDebugCss($value);
            } elseif ($key === 'debugLayout' || $key === 'debug_layout') {
                $this->setDebugLayout($value);
            } elseif ($key === 'debugLayoutLines' || $key === 'debug_layout_lines') {
                $this->setDebugLayoutLines($value);
            } elseif ($key === 'debugLayoutBlocks' || $key === 'debug_layout_blocks') {
                $this->setDebugLayoutBlocks($value);
            } elseif ($key === 'debugLayoutInline' || $key === 'debug_layout_inline') {
                $this->setDebugLayoutInline($value);
            } elseif ($key === 'debugLayoutPaddingBox' || $key === 'debug_layout_padding_box') {
                $this->setDebugLayoutPaddingBox($value);
            } elseif ($key === 'pdfBackend' || $key === 'pdf_backend') {
                $this->setPdfBackend($value);
            } elseif ($key === 'pdflibLicense' || $key === 'pdflib_license') {
                $this->setPdflibLicense($value);
            } elseif ($key === 'adminUsername' || $key === 'admin_username') {
                $this->setAdminUsername($value);
            } elseif ($key === 'adminPassword' || $key === 'admin_password') {
                $this->setAdminPassword($value);
            }
        }
        return $this;
    }

 
    public function get($key)
    {
        if ($key === 'tempDir' || $key === 'temp_dir') {
            return $this->getTempDir();
        } elseif ($key === 'fontDir' || $key === 'font_dir') {
            return $this->getFontDir();
        } elseif ($key === 'fontCache' || $key === 'font_cache') {
            return $this->getFontCache();
        } elseif ($key === 'chroot') {
            return $this->getChroot();
        } elseif ($key === 'logOutputFile' || $key === 'log_output_file') {
            return $this->getLogOutputFile();
        } elseif ($key === 'defaultMediaType' || $key === 'default_media_type') {
            return $this->getDefaultMediaType();
        } elseif ($key === 'defaultPaperSize' || $key === 'default_paper_size') {
            return $this->getDefaultPaperSize();
        } elseif ($key === 'defaultPaperOrientation' || $key === 'default_paper_orientation') {
            return $this->getDefaultPaperOrientation();
        } elseif ($key === 'defaultFont' || $key === 'default_font') {
            return $this->getDefaultFont();
        } elseif ($key === 'dpi') {
            return $this->getDpi();
        } elseif ($key === 'fontHeightRatio' || $key === 'font_height_ratio') {
            return $this->getFontHeightRatio();
        } elseif ($key === 'isPhpEnabled' || $key === 'is_php_enabled' || $key === 'enable_php') {
            return $this->getIsPhpEnabled();
        } elseif ($key === 'isRemoteEnabled' || $key === 'is_remote_enabled' || $key === 'enable_remote') {
            return $this->getIsRemoteEnabled();
        } elseif ($key === 'isJavascriptEnabled' || $key === 'is_javascript_enabled' || $key === 'enable_javascript') {
            return $this->getIsJavascriptEnabled();
        } elseif ($key === 'isHtml5ParserEnabled' || $key === 'is_html5_parser_enabled' || $key === 'enable_html5_parser') {
            return $this->getIsHtml5ParserEnabled();
        } elseif ($key === 'isFontSubsettingEnabled' || $key === 'is_font_subsetting_enabled' || $key === 'enable_font_subsetting') {
            return $this->getIsFontSubsettingEnabled();
        } elseif ($key === 'debugPng' || $key === 'debug_png') {
            return $this->getDebugPng();
        } elseif ($key === 'debugKeepTemp' || $key === 'debug_keep_temp') {
            return $this->getDebugKeepTemp();
        } elseif ($key === 'debugCss' || $key === 'debug_css') {
            return $this->getDebugCss();
        } elseif ($key === 'debugLayout' || $key === 'debug_layout') {
            return $this->getDebugLayout();
        } elseif ($key === 'debugLayoutLines' || $key === 'debug_layout_lines') {
            return $this->getDebugLayoutLines();
        } elseif ($key === 'debugLayoutBlocks' || $key === 'debug_layout_blocks') {
            return $this->getDebugLayoutBlocks();
        } elseif ($key === 'debugLayoutInline' || $key === 'debug_layout_inline') {
            return $this->getDebugLayoutInline();
        } elseif ($key === 'debugLayoutPaddingBox' || $key === 'debug_layout_padding_box') {
            return $this->getDebugLayoutPaddingBox();
        } elseif ($key === 'pdfBackend' || $key === 'pdf_backend') {
            return $this->getPdfBackend();
        } elseif ($key === 'pdflibLicense' || $key === 'pdflib_license') {
            return $this->getPdflibLicense();
        } elseif ($key === 'adminUsername' || $key === 'admin_username') {
            return $this->getAdminUsername();
        } elseif ($key === 'adminPassword' || $key === 'admin_password') {
            return $this->getAdminPassword();
        }
        return null;
    }

  
    public function setAdminPassword($adminPassword)
    {
        $this->adminPassword = $adminPassword;
        return $this;
    }

    public function getAdminPassword()
    {
        return $this->adminPassword;
    }


    public function setAdminUsername($adminUsername)
    {
        $this->adminUsername = $adminUsername;
        return $this;
    }

    public function getAdminUsername()
    {
        return $this->adminUsername;
    }


    public function setPdfBackend($pdfBackend)
    {
        $this->pdfBackend = $pdfBackend;
        return $this;
    }


    public function getPdfBackend()
    {
        return $this->pdfBackend;
    }


    public function setPdflibLicense($pdflibLicense)
    {
        $this->pdflibLicense = $pdflibLicense;
        return $this;
    }

  
    public function getPdflibLicense()
    {
        return $this->pdflibLicense;
    }

    public function setChroot($chroot)
    {
        $this->chroot = $chroot;
        return $this;
    }

   
    public function getChroot()
    {
        return $this->chroot;
    }

 
    public function setDebugCss($debugCss)
    {
        $this->debugCss = $debugCss;
        return $this;
    }

  
    public function getDebugCss()
    {
        return $this->debugCss;
    }


    public function setDebugKeepTemp($debugKeepTemp)
    {
        $this->debugKeepTemp = $debugKeepTemp;
        return $this;
    }

    public function getDebugKeepTemp()
    {
        return $this->debugKeepTemp;
    }

 
    public function setDebugLayout($debugLayout)
    {
        $this->debugLayout = $debugLayout;
        return $this;
    }


    public function getDebugLayout()
    {
        return $this->debugLayout;
    }

  
    public function setDebugLayoutBlocks($debugLayoutBlocks)
    {
        $this->debugLayoutBlocks = $debugLayoutBlocks;
        return $this;
    }

    public function getDebugLayoutBlocks()
    {
        return $this->debugLayoutBlocks;
    }


    public function setDebugLayoutInline($debugLayoutInline)
    {
        $this->debugLayoutInline = $debugLayoutInline;
        return $this;
    }

    public function getDebugLayoutInline()
    {
        return $this->debugLayoutInline;
    }


    public function setDebugLayoutLines($debugLayoutLines)
    {
        $this->debugLayoutLines = $debugLayoutLines;
        return $this;
    }

    public function getDebugLayoutLines()
    {
        return $this->debugLayoutLines;
    }

    
    public function setDebugLayoutPaddingBox($debugLayoutPaddingBox)
    {
        $this->debugLayoutPaddingBox = $debugLayoutPaddingBox;
        return $this;
    }

   
    public function getDebugLayoutPaddingBox()
    {
        return $this->debugLayoutPaddingBox;
    }

 
    public function setDebugPng($debugPng)
    {
        $this->debugPng = $debugPng;
        return $this;
    }

    public function getDebugPng()
    {
        return $this->debugPng;
    }

    turn $this
     */
    public function setDefaultFont($defaultFont)
    {
        $this->defaultFont = $defaultFont;
        return $this;
    }

   
    public function getDefaultFont()
    {
        return $this->defaultFont;
    }

   
    public function setDefaultMediaType($defaultMediaType)
    {
        $this->defaultMediaType = $defaultMediaType;
        return $this;
    }


    public function getDefaultMediaType()
    {
        return $this->defaultMediaType;
    }

   
    public function setDefaultPaperSize($defaultPaperSize)
    {
        $this->defaultPaperSize = $defaultPaperSize;
        return $this;
    }


    public function setDefaultPaperOrientation($defaultPaperOrientation)
    {
        $this->defaultPaperOrientation = $defaultPaperOrientation;
        return $this;
    }

    public function getDefaultPaperSize()
    {
        return $this->defaultPaperSize;
    }


    public function getDefaultPaperOrientation()
    {
        return $this->defaultPaperOrientation;
    }

   
    public function setDpi($dpi)
    {
        $this->dpi = $dpi;
        return $this;
    }


    public function getDpi()
    {
        return $this->dpi;
    }

    public function setFontCache($fontCache)
    {
        $this->fontCache = $fontCache;
        return $this;
    }

    public function getFontCache()
    {
        return $this->fontCache;
    }

    public function setFontDir($fontDir)
    {
        $this->fontDir = $fontDir;
        return $this;
    }

    
    public function getFontDir()
    {
        return $this->fontDir;
    }

    
    public function setFontHeightRatio($fontHeightRatio)
    {
        $this->fontHeightRatio = $fontHeightRatio;
        return $this;
    }

    
    public function getFontHeightRatio()
    {
        return $this->fontHeightRatio;
    }

 
    public function setIsFontSubsettingEnabled($isFontSubsettingEnabled)
    {
        $this->isFontSubsettingEnabled = $isFontSubsettingEnabled;
        return $this;
    }

   
    public function getIsFontSubsettingEnabled()
    {
        return $this->isFontSubsettingEnabled;
    }


    public function isFontSubsettingEnabled()
    {
        return $this->getIsFontSubsettingEnabled();
    }


    public function setIsHtml5ParserEnabled($isHtml5ParserEnabled)
    {
        $this->isHtml5ParserEnabled = $isHtml5ParserEnabled;
        return $this;
    }

   
    public function getIsHtml5ParserEnabled()
    {
        return $this->isHtml5ParserEnabled;
    }

 
    public function isHtml5ParserEnabled()
    {
        return $this->getIsHtml5ParserEnabled();
    }


    public function setIsJavascriptEnabled($isJavascriptEnabled)
    {
        $this->isJavascriptEnabled = $isJavascriptEnabled;
        return $this;
    }

    public function getIsJavascriptEnabled()
    {
        return $this->isJavascriptEnabled;
    }


    public function isJavascriptEnabled()
    {
        return $this->getIsJavascriptEnabled();
    }


    public function setIsPhpEnabled($isPhpEnabled)
    {
        $this->isPhpEnabled = $isPhpEnabled;
        return $this;
    }


    public function getIsPhpEnabled()
    {
        return $this->isPhpEnabled;
    }

 
    public function isPhpEnabled()
    {
        return $this->getIsPhpEnabled();
    }

   
    public function setIsRemoteEnabled($isRemoteEnabled)
    {
        $this->isRemoteEnabled = $isRemoteEnabled;
        return $this;
    }

  
    public function getIsRemoteEnabled()
    {
        return $this->isRemoteEnabled;
    }


    public function isRemoteEnabled()
    {
        return $this->getIsRemoteEnabled();
    }

   
    public function setLogOutputFile($logOutputFile)
    {
        $this->logOutputFile = $logOutputFile;
        return $this;
    }

    
    public function getLogOutputFile()
    {
        return $this->logOutputFile;
    }

   
    public function setTempDir($tempDir)
    {
        $this->tempDir = $tempDir;
        return $this;
    }

    public function getTempDir()
    {
        return $this->tempDir;
    }

    
    public function setRootDir($rootDir)
    {
        $this->rootDir = $rootDir;
        return $this;
    }

   
    public function getRootDir()
    {
        return $this->rootDir;
    }
}
