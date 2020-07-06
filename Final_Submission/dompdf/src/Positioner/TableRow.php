<?php


namespace Dompdf\Positioner;

use Dompdf\FrameDecorator\AbstractFrameDecorator;


class TableRow extends AbstractPositioner
{

 
    function position(AbstractFrameDecorator $frame)
    {
        $cb = $frame->get_containing_block();
        $p = $frame->get_prev_sibling();

        if ($p) {
            $y = $p->get_position("y") + $p->get_margin_height();
        } else {
            $y = $cb["y"];
        }
        $frame->set_position($cb["x"], $y);
    }
}
