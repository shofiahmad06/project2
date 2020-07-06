<?php


namespace Dompdf\Positioner;

use Dompdf\FrameDecorator\AbstractFrameDecorator;


class NullPositioner extends AbstractPositioner
{


    function position(AbstractFrameDecorator $frame)
    {
        return;
    }
}
