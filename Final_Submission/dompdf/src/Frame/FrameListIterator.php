<?php
namespace Dompdf\Frame;

use Iterator;
use Dompdf\Frame;


class FrameListIterator implements Iterator
{


    protected $_parent;

  
    protected $_cur;

    protected $_num;


    public function __construct(Frame $frame)
    {
        $this->_parent = $frame;
        $this->_cur = $frame->get_first_child();
        $this->_num = 0;
    }

    /**
     *
     */
    public function rewind()
    {
        $this->_cur = $this->_parent->get_first_child();
        $this->_num = 0;
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return isset($this->_cur); // && ($this->_cur->get_prev_sibling() === $this->_prev);
    }

    /**
     * @return int
     */
    public function key()
    {
        return $this->_num;
    }

    /**
     * @return Frame
     */
    public function current()
    {
        return $this->_cur;
    }

    /**
     * @return Frame
     */
    public function next()
    {
        $ret = $this->_cur;
        if (!$ret) {
            return null;
        }

        $this->_cur = $this->_cur->get_next_sibling();
        $this->_num++;
        return $ret;
    }
}
