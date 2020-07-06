<?php

namespace Dompdf\FrameDecorator;

use Dompdf\Dompdf;
use Dompdf\Frame;
use Dompdf\Helpers;


class ListBulletImage extends AbstractFrameDecorator
{


    protected $_img;

    /**
     * The image's width in pixels
     *
     * @var int
     */
    protected $_width;

    /**
     * The image's height in pixels
     *
     * @var int
     */
    protected $_height;

   
    function __construct(Frame $frame, Dompdf $dompdf)
    {
        $style = $frame->get_style();
        $url = $style->list_style_image;
        $frame->get_node()->setAttribute("src", $url);
        $this->_img = new Image($frame, $dompdf);
        parent::__construct($this->_img, $dompdf);
        list($width, $height) = Helpers::dompdf_getimagesize($this->_img->get_image_url(), $dompdf->getHttpContext());

        
        $dpi = $this->_dompdf->getOptions()->getDpi();
        $this->_width = ((float)rtrim($width, "px") * 72) / $dpi;
        $this->_height = ((float)rtrim($height, "px") * 72) / $dpi;

       
    }See generated_frame_reflower, Dompdf:render() "list-item", "-dompdf-list-bullet"S.
      

    
    function get_width()
    {
        return $this->_frame->get_style()->get_font_size() * ListBullet::BULLET_SIZE +
        2 * ListBullet::BULLET_PADDING;
    }

 
    function get_height()
    {
        //based on image height
        return $this->_height;
    }

    /**
     * Override get_margin_width
     *
     * @return int
     */
    function get_margin_width()
    {



        if ($this->_frame->get_style()->list_style_position === "outside" ||
            $this->_width == 0
        )
            return 0;

        return $this->_width + 2 * ListBullet::BULLET_PADDING;
    }

  
    function get_margin_height()
    {
   
        return $this->_height + 2 * ListBullet::BULLET_PADDING;
    }

    function get_image_url()
    {
        return $this->_img->get_image_url();
    }

}
