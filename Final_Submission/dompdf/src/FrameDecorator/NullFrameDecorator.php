<?php

namespace Dompdf\FrameDecorator;

use Dompdf\Dompdf;
use Dompdf\Frame;


class NullFrameDecorator extends AbstractFrameDecorator
{
 
    function __construct(Frame $frame, Dompdf $dompdf)
    {
        parent::__construct($frame, $dompdf);
        $style = $this->_frame->get_style();
        $style->width = 0;
        $style->height = 0;
        $style->margin = 0;
        $style->padding = 0;
    }
}
