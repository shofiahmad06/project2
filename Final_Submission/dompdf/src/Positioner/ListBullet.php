<?php


namespace Dompdf\Positioner;

use Dompdf\FrameDecorator\AbstractFrameDecorator;


class ListBullet extends AbstractPositioner
{

 
    function position(AbstractFrameDecorator $frame)
    {

     
        $cb = $frame->get_containing_block();

        
        $x = $cb["x"] - $frame->get_width();

        $p = $frame->find_block_parent();

        $y = $p->get_current_line_box()->y;

        // This is a bit of a hack...
        $n = $frame->get_next_sibling();
        if ($n) {
            $style = $n->get_style();
            $line_height = $style->length_in_pt($style->line_height, $style->get_font_size());
            $offset = (float)$style->length_in_pt($line_height, $n->get_containing_block("h")) - $frame->get_height();
            $y += $offset / 2;
        }

       
        $frame->set_position($x, $y);
    }
}
