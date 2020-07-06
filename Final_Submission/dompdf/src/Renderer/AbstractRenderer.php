<?php

namespace Dompdf\Renderer;

use Dompdf\Adapter\CPDF;
use Dompdf\Css\Color;
use Dompdf\Css\Style;
use Dompdf\Dompdf;
use Dompdf\Helpers;
use Dompdf\Frame;
use Dompdf\Image\Cache;


abstract class AbstractRenderer
{


    protected $_canvas;


    protected $_dompdf;


    function __construct(Dompdf $dompdf)
    {
        $this->_dompdf = $dompdf;
        $this->_canvas = $dompdf->getCanvas();
    }

    abstract function render(Frame $frame);


    protected function _background_image($url, $x, $y, $width, $height, $style)
    {
        if (!function_exists("imagecreatetruecolor")) {
            throw new \Exception("The PHP GD extension is required, but is not installed.");
        }

        $sheet = $style->get_stylesheet();

        
        if ($width == 0 || $height == 0) {
            return;
        }

        $box_width = $width;
        $box_height = $height;

        //debugpng
        if ($this->_dompdf->getOptions()->getDebugPng()) {
            print '[_background_image ' . $url . ']';
        }

        list($img, $type, /*$msg*/) = Cache::resolve_url(
            $url,
            $sheet->get_protocol(),
            $sheet->get_host(),
            $sheet->get_base_path(),
            $this->_dompdf
        );

        // Bail if the image is no good
        if (Cache::is_broken($img)) {
            return;
        }

      
        list($img_w, $img_h) = Helpers::dompdf_getimagesize($img, $this->_dompdf->getHttpContext());
        if (!isset($img_w) || $img_w == 0 || !isset($img_h) || $img_h == 0) {
            return;
        }

        $repeat = $style->background_repeat;
        $dpi = $this->_dompdf->getOptions()->getDpi();

        $bg_width = round((float)($width * $dpi) / 72);
        $bg_height = round((float)($height * $dpi) / 72);

     

        list($bg_x, $bg_y) = $style->background_position;

        if (Helpers::is_percent($bg_x)) {
            // The point $bg_x % from the left edge of the image is placed
            // $bg_x % from the left edge of the background rectangle
            $p = ((float)$bg_x) / 100.0;
            $x1 = $p * $img_w;
            $x2 = $p * $bg_width;

            $bg_x = $x2 - $x1;
        } else {
            $bg_x = (float)($style->length_in_pt($bg_x) * $dpi) / 72;
        }

        $bg_x = round($bg_x + (float)$style->length_in_pt($style->border_left_width) * $dpi / 72);

        if (Helpers::is_percent($bg_y)) {
            // The point $bg_y % from the left edge of the image is placed
            // $bg_y % from the left edge of the background rectangle
            $p = ((float)$bg_y) / 100.0;
            $y1 = $p * $img_h;
            $y2 = $p * $bg_height;

            $bg_y = $y2 - $y1;
        } else {
            $bg_y = (float)($style->length_in_pt($bg_y) * $dpi) / 72;
        }

        $bg_y = round($bg_y + (float)$style->length_in_pt($style->border_top_width) * $dpi / 72);

      
        if ($repeat !== "repeat" && $repeat !== "repeat-x") {
            //No repeat x
            if ($bg_x < 0) {
                $bg_width = $img_w + $bg_x;
            } else {
                $x += ($bg_x * 72) / $dpi;
                $bg_width = $bg_width - $bg_x;
                if ($bg_width > $img_w) {
                    $bg_width = $img_w;
                }
                $bg_x = 0;
            }

            if ($bg_width <= 0) {
                return;
            }

            $width = (float)($bg_width * 72) / $dpi;
        } else {
            //repeat x
            if ($bg_x < 0) {
                $bg_x = -((-$bg_x) % $img_w);
            } else {
                $bg_x = $bg_x % $img_w;
                if ($bg_x > 0) {
                    $bg_x -= $img_w;
                }
            }
        }

        if ($repeat !== "repeat" && $repeat !== "repeat-y") {
            //no repeat y
            if ($bg_y < 0) {
                $bg_height = $img_h + $bg_y;
            } else {
                $y += ($bg_y * 72) / $dpi;
                $bg_height = $bg_height - $bg_y;
                if ($bg_height > $img_h) {
                    $bg_height = $img_h;
                }
                $bg_y = 0;
            }
            if ($bg_height <= 0) {
                return;
            }
            $height = (float)($bg_height * 72) / $dpi;
        } else {
            //repeat y
            if ($bg_y < 0) {
                $bg_y = -((-$bg_y) % $img_h);
            } else {
                $bg_y = $bg_y % $img_h;
                if ($bg_y > 0) {
                    $bg_y -= $img_h;
                }
            }
        }

        //Optimization, if repeat has no effect
        if ($repeat === "repeat" && $bg_y <= 0 && $img_h + $bg_y >= $bg_height) {
            $repeat = "repeat-x";
        }

        if ($repeat === "repeat" && $bg_x <= 0 && $img_w + $bg_x >= $bg_width) {
            $repeat = "repeat-y";
        }

        if (($repeat === "repeat-x" && $bg_x <= 0 && $img_w + $bg_x >= $bg_width) ||
            ($repeat === "repeat-y" && $bg_y <= 0 && $img_h + $bg_y >= $bg_height)
        ) {
            $repeat = "no-repeat";
        }

      
        $filedummy = $img;

        $is_png = false;
        $filedummy .= '_' . $bg_width . '_' . $bg_height . '_' . $bg_x . '_' . $bg_y . '_' . $repeat;


        if ($this->_canvas instanceof CPDF &&
            $this->_canvas->get_cpdf()->image_iscached($filedummy)
        ) {
            $bg = null;
        } else {
            // Create a new image to fit over the background rectangle
            $bg = imagecreatetruecolor($bg_width, $bg_height);

            switch (strtolower($type)) {
                case "png":
                    $is_png = true;
                    imagesavealpha($bg, true);
                    imagealphablending($bg, false);
                    $src = imagecreatefrompng($img);
                    break;

                case "jpeg":
                    $src = imagecreatefromjpeg($img);
                    break;

                case "gif":
                    $src = imagecreatefromgif($img);
                    break;

                case "bmp":
                    $src = Helpers::imagecreatefrombmp($img);
                    break;

                default:
                    return; // Unsupported image type
            }

            if ($src == null) {
                return;
            }

       
            $ti = imagecolortransparent($src);

            if ($ti >= 0) {
                $tc = imagecolorsforindex($src, $ti);
                $ti = imagecolorallocate($bg, $tc['red'], $tc['green'], $tc['blue']);
                imagefill($bg, 0, 0, $ti);
                imagecolortransparent($bg, $ti);
            }

            if ($bg_x < 0) {
                $dst_x = 0;
                $src_x = -$bg_x;
            } else {
                $src_x = 0;
                $dst_x = $bg_x;
            }

            if ($bg_y < 0) {
                $dst_y = 0;
                $src_y = -$bg_y;
            } else {
                $src_y = 0;
                $dst_y = $bg_y;
            }

        
            $start_y = $bg_y;

         
            if ($repeat === "no-repeat") {
            
                imagecopy($bg, $src, $dst_x, $dst_y, $src_x, $src_y, $img_w, $img_h);

            } else if ($repeat === "repeat-x") {
                for ($bg_x = $start_x; $bg_x < $bg_width; $bg_x += $img_w) {
                    if ($bg_x < 0) {
                        $dst_x = 0;
                        $src_x = -$bg_x;
                        $w = $img_w + $bg_x;
                    } else {
                        $dst_x = $bg_x;
                        $src_x = 0;
                        $w = $img_w;
                    }
                    imagecopy($bg, $src, $dst_x, $dst_y, $src_x, $src_y, $w, $img_h);
                }
            } else if ($repeat === "repeat-y") {

                for ($bg_y = $start_y; $bg_y < $bg_height; $bg_y += $img_h) {
                    if ($bg_y < 0) {
                        $dst_y = 0;
                        $src_y = -$bg_y;
                        $h = $img_h + $bg_y;
                    } else {
                        $dst_y = $bg_y;
                        $src_y = 0;
                        $h = $img_h;
                    }
                    imagecopy($bg, $src, $dst_x, $dst_y, $src_x, $src_y, $img_w, $h);
                }
            } else if ($repeat === "repeat") {
                for ($bg_y = $start_y; $bg_y < $bg_height; $bg_y += $img_h) {
                    for ($bg_x = $start_x; $bg_x < $bg_width; $bg_x += $img_w) {
                        if ($bg_x < 0) {
                            $dst_x = 0;
                            $src_x = -$bg_x;
                            $w = $img_w + $bg_x;
                        } else {
                            $dst_x = $bg_x;
                            $src_x = 0;
                            $w = $img_w;
                        }

                        if ($bg_y < 0) {
                            $dst_y = 0;
                            $src_y = -$bg_y;
                            $h = $img_h + $bg_y;
                        } else {
                            $dst_y = $bg_y;
                            $src_y = 0;
                            $h = $img_h;
                        }
                        imagecopy($bg, $src, $dst_x, $dst_y, $src_x, $src_y, $w, $h);
                    }
                }
            } else {
                print 'Unknown repeat!';
            }

            imagedestroy($src);

        } /* End optimize away creation of duplicates */

        $this->_canvas->clipping_rectangle($x, $y, $box_width, $box_height);

      
        if (!$is_png && $this->_canvas instanceof CPDF) {
            // Note: CPDF_Adapter image converts y position
            $this->_canvas->get_cpdf()->addImagePng($filedummy, $x, $this->_canvas->get_height() - $y - $height, $width, $height, $bg);
        } else {
            $tmp_dir = $this->_dompdf->getOptions()->getTempDir();
            $tmp_name = tempnam($tmp_dir, "bg_dompdf_img_");
            @unlink($tmp_name);
            $tmp_file = "$tmp_name.png";

            //debugpng
            if ($this->_dompdf->getOptions()->getDebugPng()) print '[_background_image ' . $tmp_file . ']';

            imagepng($bg, $tmp_file);
            $this->_canvas->image($tmp_file, $x, $y, $width, $height);
            imagedestroy($bg);

            //debugpng
            if ($this->_dompdf->getOptions()->getDebugPng()) {
                print '[_background_image unlink ' . $tmp_file . ']';
            }

            if (!$this->_dompdf->getOptions()->getDebugKeepTemp()) {
                unlink($tmp_file);
            }
        }

        $this->_canvas->clipping_end();
    }


    protected function _get_dash_pattern($style, $width)
    {
        $pattern = array();

        switch ($style) {
            default:
             
            case "none":
                break;

            case "dotted":
                if ($width <= 1)
                    $pattern = array($width, $width * 2);
                else
                    $pattern = array($width);
                break;

            case "dashed":
                $pattern = array(3 * $width);
                break;
        }

        return $pattern;
    }

 
    protected function _border_none($x, $y, $length, $color, $widths, $side, $corner_style = "bevel", $r1 = 0, $r2 = 0)
    {
        return;
    }

  
    protected function _border_hidden($x, $y, $length, $color, $widths, $side, $corner_style = "bevel", $r1 = 0, $r2 = 0)
    {
        return;
    }

 
    protected function _border_dotted($x, $y, $length, $color, $widths, $side, $corner_style = "bevel", $r1 = 0, $r2 = 0)
    {
        $this->_border_line($x, $y, $length, $color, $widths, $side, $corner_style, "dotted", $r1, $r2);
    }


   
    protected function _border_dashed($x, $y, $length, $color, $widths, $side, $corner_style = "bevel", $r1 = 0, $r2 = 0)
    {
        $this->_border_line($x, $y, $length, $color, $widths, $side, $corner_style, "dashed", $r1, $r2);
    }


  
    protected function _border_solid($x, $y, $length, $color, $widths, $side, $corner_style = "bevel", $r1 = 0, $r2 = 0)
    {
        // TODO: Solve rendering where one corner is beveled (radius == 0), one corner isn't.
        if ($corner_style !== "bevel" || $r1 > 0 || $r2 > 0) {
            // do it the simple way
            $this->_border_line($x, $y, $length, $color, $widths, $side, $corner_style, "solid", $r1, $r2);
            return;
        }

        list($top, $right, $bottom, $left) = $widths;

        // All this polygon business is for beveled corners...
        switch ($side) {
            case "top":
                $points = array($x, $y,
                    $x + $length, $y,
                    $x + $length - $right, $y + $top,
                    $x + $left, $y + $top);
                $this->_canvas->polygon($points, $color, null, null, true);
                break;

            case "bottom":
                $points = array($x, $y,
                    $x + $length, $y,
                    $x + $length - $right, $y - $bottom,
                    $x + $left, $y - $bottom);
                $this->_canvas->polygon($points, $color, null, null, true);
                break;

            case "left":
                $points = array($x, $y,
                    $x, $y + $length,
                    $x + $left, $y + $length - $bottom,
                    $x + $left, $y + $top);
                $this->_canvas->polygon($points, $color, null, null, true);
                break;

            case "right":
                $points = array($x, $y,
                    $x, $y + $length,
                    $x - $right, $y + $length - $bottom,
                    $x - $right, $y + $top);
                $this->_canvas->polygon($points, $color, null, null, true);
                break;

            default:
                return;
        }
    }

  
    protected function _apply_ratio($side, $ratio, $top, $right, $bottom, $left, &$x, &$y, &$length, &$r1, &$r2)
    {
        switch ($side) {
            case "top":
                $r1 -= $left * $ratio;
                $r2 -= $right * $ratio;
                $x += $left * $ratio;
                $y += $top * $ratio;
                $length -= $left * $ratio + $right * $ratio;
                break;

            case "bottom":
                $r1 -= $right * $ratio;
                $r2 -= $left * $ratio;
                $x += $left * $ratio;
                $y -= $bottom * $ratio;
                $length -= $left * $ratio + $right * $ratio;
                break;

            case "left":
                $r1 -= $top * $ratio;
                $r2 -= $bottom * $ratio;
                $x += $left * $ratio;
                $y += $top * $ratio;
                $length -= $top * $ratio + $bottom * $ratio;
                break;

            case "right":
                $r1 -= $bottom * $ratio;
                $r2 -= $top * $ratio;
                $x -= $right * $ratio;
                $y += $top * $ratio;
                $length -= $top * $ratio + $bottom * $ratio;
                break;

            default:
                return;
        }
    }

   
    protected function _border_double($x, $y, $length, $color, $widths, $side, $corner_style = "bevel", $r1 = 0, $r2 = 0)
    {
        list($top, $right, $bottom, $left) = $widths;

        $third_widths = array($top / 3, $right / 3, $bottom / 3, $left / 3);

        // draw the outer border
        $this->_border_solid($x, $y, $length, $color, $third_widths, $side, $corner_style, $r1, $r2);

        $this->_apply_ratio($side, 2 / 3, $top, $right, $bottom, $left, $x, $y, $length, $r1, $r2);

        $this->_border_solid($x, $y, $length, $color, $third_widths, $side, $corner_style, $r1, $r2);
    }

   
    protected function _border_groove($x, $y, $length, $color, $widths, $side, $corner_style = "bevel", $r1 = 0, $r2 = 0)
    {
        list($top, $right, $bottom, $left) = $widths;

        $half_widths = array($top / 2, $right / 2, $bottom / 2, $left / 2);

        $this->_border_inset($x, $y, $length, $color, $half_widths, $side, $corner_style, $r1, $r2);

        $this->_apply_ratio($side, 0.5, $top, $right, $bottom, $left, $x, $y, $length, $r1, $r2);

        $this->_border_outset($x, $y, $length, $color, $half_widths, $side, $corner_style, $r1, $r2);

    }

    
    protected function _border_ridge($x, $y, $length, $color, $widths, $side, $corner_style = "bevel", $r1 = 0, $r2 = 0)
    {
        list($top, $right, $bottom, $left) = $widths;

        $half_widths = array($top / 2, $right / 2, $bottom / 2, $left / 2);

        $this->_border_outset($x, $y, $length, $color, $half_widths, $side, $corner_style, $r1, $r2);

        $this->_apply_ratio($side, 0.5, $top, $right, $bottom, $left, $x, $y, $length, $r1, $r2);

        $this->_border_inset($x, $y, $length, $color, $half_widths, $side, $corner_style, $r1, $r2);

    }


    protected function _tint($c)
    {
        if (!is_numeric($c))
            return $c;

        return min(1, $c + 0.16);
    }

    protected function _shade($c)
    {
        if (!is_numeric($c))
            return $c;

        return max(0, $c - 0.33);
    }

 
    protected function _border_inset($x, $y, $length, $color, $widths, $side, $corner_style = "bevel", $r1 = 0, $r2 = 0)
    {
        switch ($side) {
            case "top":
            case "left":
                $shade = array_map(array($this, "_shade"), $color);
                $this->_border_solid($x, $y, $length, $shade, $widths, $side, $corner_style, $r1, $r2);
                break;

            case "bottom":
            case "right":
                $tint = array_map(array($this, "_tint"), $color);
                $this->_border_solid($x, $y, $length, $tint, $widths, $side, $corner_style, $r1, $r2);
                break;

            default:
                return;
        }
    }


    protected function _border_outset($x, $y, $length, $color, $widths, $side, $corner_style = "bevel", $r1 = 0, $r2 = 0)
    {
        switch ($side) {
            case "top":
            case "left":
                $tint = array_map(array($this, "_tint"), $color);
                $this->_border_solid($x, $y, $length, $tint, $widths, $side, $corner_style, $r1, $r2);
                break;

            case "bottom":
            case "right":
                $shade = array_map(array($this, "_shade"), $color);
                $this->_border_solid($x, $y, $length, $shade, $widths, $side, $corner_style, $r1, $r2);
                break;

            default:
                return;
        }
    }


    protected function _border_line($x, $y, $length, $color, $widths, $side, $corner_style = "bevel", $pattern_name, $r1 = 0, $r2 = 0)
    {
        /** used by $$side */
        list($top, $right, $bottom, $left) = $widths;
        $width = $$side;

        $pattern = $this->_get_dash_pattern($pattern_name, $width);

        $half_width = $width / 2;
        $r1 -= $half_width;
        $r2 -= $half_width;
        $adjust = $r1 / 80;
        $length -= $width;

        switch ($side) {
            case "top":
                $x += $half_width;
                $y += $half_width;

                if ($r1 > 0) {
                    $this->_canvas->arc($x + $r1, $y + $r1, $r1, $r1, 90 - $adjust, 135 + $adjust, $color, $width, $pattern);
                }

                $this->_canvas->line($x + $r1, $y, $x + $length - $r2, $y, $color, $width, $pattern);

                if ($r2 > 0) {
                    $this->_canvas->arc($x + $length - $r2, $y + $r2, $r2, $r2, 45 - $adjust, 90 + $adjust, $color, $width, $pattern);
                }
                break;

            case "bottom":
                $x += $half_width;
                $y -= $half_width;

                if ($r1 > 0) {
                    $this->_canvas->arc($x + $r1, $y - $r1, $r1, $r1, 225 - $adjust, 270 + $adjust, $color, $width, $pattern);
                }

                $this->_canvas->line($x + $r1, $y, $x + $length - $r2, $y, $color, $width, $pattern);

                if ($r2 > 0) {
                    $this->_canvas->arc($x + $length - $r2, $y - $r2, $r2, $r2, 270 - $adjust, 315 + $adjust, $color, $width, $pattern);
                }
                break;

            case "left":
                $y += $half_width;
                $x += $half_width;

                if ($r1 > 0) {
                    $this->_canvas->arc($x + $r1, $y + $r1, $r1, $r1, 135 - $adjust, 180 + $adjust, $color, $width, $pattern);
                }

                $this->_canvas->line($x, $y + $r1, $x, $y + $length - $r2, $color, $width, $pattern);

                if ($r2 > 0) {
                    $this->_canvas->arc($x + $r2, $y + $length - $r2, $r2, $r2, 180 - $adjust, 225 + $adjust, $color, $width, $pattern);
                }
                break;

            case "right":
                $y += $half_width;
                $x -= $half_width;

                if ($r1 > 0) {
                    $this->_canvas->arc($x - $r1, $y + $r1, $r1, $r1, 0 - $adjust, 45 + $adjust, $color, $width, $pattern);
                }

                $this->_canvas->line($x, $y + $r1, $x, $y + $length - $r2, $color, $width, $pattern);

                if ($r2 > 0) {
                    $this->_canvas->arc($x - $r2, $y + $length - $r2, $r2, $r2, 315 - $adjust, 360 + $adjust, $color, $width, $pattern);
                }
                break;
        }
    }

    protected function _set_opacity($opacity)
    {
        if (is_numeric($opacity) && $opacity <= 1.0 && $opacity >= 0.0) {
            $this->_canvas->set_opacity($opacity);
        }
    }


    protected function _debug_layout($box, $color = "red", $style = array())
    {
        $this->_canvas->rectangle($box[0], $box[1], $box[2], $box[3], Color::parse($color), 0.1, $style);
    }
}
