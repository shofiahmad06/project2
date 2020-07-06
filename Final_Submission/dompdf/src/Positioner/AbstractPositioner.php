<?php


namespace Dompdf\Positioner;

use Dompdf\FrameDecorator\AbstractFrameDecorator;


abstract class AbstractPositioner
{


    abstract function position(AbstractFrameDecorator $frame);


    function move(AbstractFrameDecorator $frame, $offset_x, $offset_y, $ignore_self = false)
    {
        list($x, $y) = $frame->get_position();

        if (!$ignore_self) {
            $frame->set_position($x + $offset_x, $y + $offset_y);
        }

        foreach ($frame->get_children() as $child) {
            $child->move($offset_x, $offset_y);
        }
    }
}
