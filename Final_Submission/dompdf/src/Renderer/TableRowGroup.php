<?php

namespace Dompdf\Renderer;

use Dompdf\Frame;


class TableRowGroup extends Block
{

  
    function render(Frame $frame)
    {
        $style = $frame->get_style();

        $this->_set_opacity($frame->get_opacity($style->opacity));

        $this->_render_border($frame);
        $this->_render_outline($frame);

        if ($this->_dompdf->getOptions()->getDebugLayout() && $this->_dompdf->getOptions()->getDebugLayoutBlocks()) {
            $this->_debug_layout($frame->get_border_box(), "red");
            if ($this->_dompdf->getOptions()->getDebugLayoutPaddingBox()) {
                $this->_debug_layout($frame->get_padding_box(), "red", array(0.5, 0.5));
            }
        }

        if ($this->_dompdf->getOptions()->getDebugLayout() && $this->_dompdf->getOptions()->getDebugLayoutLines() && $frame->get_decorator()) {
            foreach ($frame->get_decorator()->get_line_boxes() as $line) {
                $frame->_debug_layout(array($line->x, $line->y, $line->w, $line->h), "orange");
            }
        }

        $id = $frame->get_node()->getAttribute("id");
        if (strlen($id) > 0)  {
            $this->_canvas->add_named_dest($id);
        }
    }
}
