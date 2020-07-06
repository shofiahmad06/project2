<?php

namespace Dompdf\FrameDecorator;

use Dompdf\Dompdf;
use Dompdf\Frame;
use Dompdf\FontMetrics;
use Dompdf\Image\Cache;


class Image extends AbstractFrameDecorator
{


    protected $_image_url;

    protected $_image_msg;

    function __construct(Frame $frame, Dompdf $dompdf)
    {
        parent::__construct($frame, $dompdf);
        $url = $frame->get_node()->getAttribute("src");

        $debug_png = $dompdf->getOptions()->getDebugPng();
        if ($debug_png) print '[__construct ' . $url . ']';

        list($this->_image_url, /*$type*/, $this->_image_msg) = Cache::resolve_url(
            $url,
            $dompdf->getProtocol(),
            $dompdf->getBaseHost(),
            $dompdf->getBasePath(),
            $dompdf
        );

        if (Cache::is_broken($this->_image_url) &&
            $alt = $frame->get_node()->getAttribute("alt")
        ) {
            $style = $frame->get_style();
            $style->width = (4 / 3) * $dompdf->getFontMetrics()->getTextWidth($alt, $style->font_family, $style->font_size, $style->word_spacing);
            $style->height = $dompdf->getFontMetrics()->getFontHeight($style->font_family, $style->font_size);
        }
    }

    function get_image_url()
    {
        return $this->_image_url;
    }


    function get_image_msg()
    {
        return $this->_image_msg;
    }

}
