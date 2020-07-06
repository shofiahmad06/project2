<?php

namespace Dompdf\FrameDecorator;

use Dompdf\Dompdf;
use Dompdf\Frame;


class TableRowGroup extends AbstractFrameDecorator
{


    function __construct(Frame $frame, Dompdf $dompdf)
    {
        parent::__construct($frame, $dompdf);
    }

 
    function split(Frame $child = null, $force_pagebreak = false)
    {
        if (is_null($child)) {
            parent::split();
            return;
        }

        // Remove child & all subsequent rows from the cellmap
        $cellmap = $this->get_parent()->get_cellmap();
        $iter = $child;

        while ($iter) {
            $cellmap->remove_row($iter);
            $iter = $iter->get_next_sibling();
        }

        
        if ($child === $this->get_first_child()) {
            $cellmap->remove_row_group($this);
            parent::split();
            return;
        }

        $cellmap->update_row_group($this, $child->get_prev_sibling());
        parent::split($child);
    }
}

