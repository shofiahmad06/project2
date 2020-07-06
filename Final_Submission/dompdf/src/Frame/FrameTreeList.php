<?php
namespace Dompdf\Frame;

use IteratorAggregate;
use Dompdf\Frame;


class FrameTreeList implements IteratorAggregate
{
    
    protected $_root;

   
    public function __construct(Frame $root)
    {
        $this->_root = $root;
    }

    
    public function getIterator()
    {
        return new FrameTreeIterator($this->_root);
    }
}
