<?php


namespace Dompdf\Positioner;

use Dompdf\FrameDecorator\AbstractFrameDecorator;


class Block extends AbstractPositioner {

    function position(AbstractFrameDecorator $frame)
    {
        $style = $frame->get_style();
        $cb = $frame->get_containing_block();
        $p = $frame->find_block_parent();

        if ($p) {
            $float = $style->float;

            if (!$float || $float === "none") {
                $p->add_line(true);
            }
            $y = $p->get_current_line_box()->y;

        } else {
            $y = $cb["y"];
        }

        $x = $cb["x"];

        if ($style->position === "relative") {
            $top = (float)$style->length_in_pt($style->top, $cb["h"]);
           
            $left = (float)$style->length_in_pt($style->left, $cb["w"]);

            $x += $left;
            $y += $top;
        }

        $frame->set_position($x, $y);
    }
}
