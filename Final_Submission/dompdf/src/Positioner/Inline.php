<?php


namespace Dompdf\Positioner;

use Dompdf\FrameDecorator\AbstractFrameDecorator;
use Dompdf\FrameDecorator\Inline as InlineFrameDecorator;
use Dompdf\FrameDecorator\Block as BlockFrameDecorator;
use Dompdf\Exception;


class Inline extends AbstractPositioner
{


    function position(AbstractFrameDecorator $frame)
    {

        $p = $frame->find_block_parent();



        if (!$p) {
            throw new Exception("No block-level parent found.  Not good.");
        }

        $f = $frame;

        $cb = $f->get_containing_block();
        $line = $p->get_current_line_box();

       
        $is_fixed = false;
        while ($f = $f->get_parent()) {
            if ($f->get_style()->position === "fixed") {
                $is_fixed = true;
                break;
            }
        }

        $f = $frame;

        if (!$is_fixed && $f->get_parent() &&
            $f->get_parent() instanceof InlineFrameDecorator &&
            $f->is_text_node()
        ) {
            $min_max = $f->get_reflower()->get_min_max_width();

          
            if ($min_max["min"] > ($cb["w"] - $line->left - $line->w - $line->right)) {
                $p->add_line();
            }
        }

        $f->set_position($cb["x"] + $line->w, $line->y);
    }
}
