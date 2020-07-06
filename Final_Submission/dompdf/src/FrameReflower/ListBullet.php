<?php

namespace Dompdf\FrameReflower;

use Dompdf\FrameDecorator\Block as BlockFrameDecorator;
use Dompdf\FrameDecorator\AbstractFrameDecorator;


class ListBullet extends AbstractFrameReflower
{


    function __construct(AbstractFrameDecorator $frame)
    {
        parent::__construct($frame);
    }

 
    function reflow(BlockFrameDecorator $block = null)
    {
        $style = $this->_frame->get_style();

        $style->width = $this->_frame->get_width();
        $this->_frame->position();

        if ($style->list_style_position === "inside") {
            $p = $this->_frame->find_block_parent();
            $p->add_frame_to_line($this->_frame);
        }
    }
}
