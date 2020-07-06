<?php
namespace Dompdf;

use Dompdf\Frame;


    protected $_canvas;

    public function __construct(Canvas $canvas)
    {
        $this->_canvas = $canvas;
    }

  
    public function evaluate($code, $vars = array())
    {
        if (!$this->_canvas->get_dompdf()->getOptions()->getIsPhpEnabled()) {
            return;
        }

        // Set up some variables for the inline code
        $pdf = $this->_canvas;
        $fontMetrics = $pdf->get_dompdf()->getFontMetrics();
        $PAGE_NUM = $pdf->get_page_number();
        $PAGE_COUNT = $pdf->get_page_count();

        // Override those variables if passed in
        foreach ($vars as $k => $v) {
            $$k = $v;
        }

        eval($code);
    }

    public function render(Frame $frame)
    {
        $this->evaluate($frame->get_node()->nodeValue);
    }
}
