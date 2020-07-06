<?php

namespace Dompdf\FrameDecorator;

use Dompdf\Dompdf;
use Dompdf\Frame;
use Dompdf\Exception;
use DOMText;
use Dompdf\FontMetrics;


class Text extends AbstractFrameDecorator
{

    // protected members
    protected $_text_spacing;


    function __construct(Frame $frame, Dompdf $dompdf)
    {
        if (!$frame->is_text_node()) {
            throw new Exception("Text_Decorator can only be applied to #text nodes.");
        }

        parent::__construct($frame, $dompdf);
        $this->_text_spacing = null;
    }

    function reset()
    {
        parent::reset();
        $this->_text_spacing = null;
    }

    function get_text_spacing()
    {
        return $this->_text_spacing;
    }

    /**
     * @return string
     */
    function get_text()
    {
     

        return $this->_frame->get_node()->data;
    }

    
    function get_margin_height()
    {
        
        $style = $this->get_parent()->get_style();
        $font = $style->font_family;
        $size = $style->font_size;


        return ($style->line_height / ($size > 0 ? $size : 1)) * $this->_dompdf->getFontMetrics()->getFontHeight($font, $size);
    }


    function get_padding_box()
    {
        $pb = $this->_frame->get_padding_box();
        $pb[3] = $pb["h"] = $this->_frame->get_style()->height;

        return $pb;
    }


    function set_text_spacing($spacing)
    {
        $style = $this->_frame->get_style();

        $this->_text_spacing = $spacing;
        $char_spacing = (float)$style->length_in_pt($style->letter_spacing);

        // Re-adjust our width to account for the change in spacing
        $style->width = $this->_dompdf->getFontMetrics()->getTextWidth($this->get_text(), $style->font_family, $style->font_size, $spacing, $char_spacing);
    }

    function recalculate_width()
    {
        $style = $this->get_style();
        $text = $this->get_text();
        $size = $style->font_size;
        $font = $style->font_family;
        $word_spacing = (float)$style->length_in_pt($style->word_spacing);
        $char_spacing = (float)$style->length_in_pt($style->letter_spacing);

        return $style->width = $this->_dompdf->getFontMetrics()->getTextWidth($text, $font, $size, $word_spacing, $char_spacing);
    }

    function split_text($offset)
    {
        if ($offset == 0) {
            return null;
        }

        $split = $this->_frame->get_node()->splitText($offset);

        $deco = $this->copy($split);

        $p = $this->get_parent();
        $p->insert_child_after($deco, $this, false);

        if ($p instanceof Inline) {
            $p->split($deco);
        }

        return $deco;
    }


    function delete_text($offset, $count)
    {
        $this->_frame->get_node()->deleteData($offset, $count);
    }

    function set_text($text)
    {
        $this->_frame->get_node()->data = $text;
    }
}
