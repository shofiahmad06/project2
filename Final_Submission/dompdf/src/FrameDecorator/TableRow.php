<?php

namespace Dompdf\FrameDecorator;

use Dompdf\Dompdf;
use Dompdf\Frame;
use Dompdf\FrameDecorator\Table as TableFrameDecorator;


class TableRow extends AbstractFrameDecorator
{

    function __construct(Frame $frame, Dompdf $dompdf)
    {
        parent::__construct($frame, $dompdf);
    }

  
    function normalise()
    {
        // Find our table parent
        $p = TableFrameDecorator::find_parent_table($this);

        $erroneous_frames = array();
        foreach ($this->get_children() as $child) {
            $display = $child->get_style()->display;

            if ($display !== "table-cell")
                $erroneous_frames[] = $child;
        }

        //  dump the extra nodes after the table.
        foreach ($erroneous_frames as $frame)
            $p->move_after($frame);
    }
}
