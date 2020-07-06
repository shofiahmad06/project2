<?php


namespace Dompdf\FrameReflower;

use Dompdf\Frame;
use Dompdf\FrameDecorator\Block as BlockFrameDecorator;


class NullFrameReflower extends AbstractFrameReflower
{


    function __construct(Frame $frame)
    {
        parent::__construct($frame);
    }

   
    function reflow(BlockFrameDecorator $block = null)
    {
        return;
    }

}
