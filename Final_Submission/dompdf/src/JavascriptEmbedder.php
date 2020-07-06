<?php
namespace Dompdf;

use Dompdf\Frame;


class JavascriptEmbedder
{

    protected $_dompdf;

   
    public function __construct(Dompdf $dompdf)
    {
        $this->_dompdf = $dompdf;
    }

 
    public function insert($script)
    {
        $this->_dompdf->getCanvas()->javascript($script);
    }

   
    public function render(Frame $frame)
    {
        if (!$this->_dompdf->getOptions()->getIsJavascriptEnabled()) {
            return;
        }

        $this->insert($frame->get_node()->nodeValue);
    }
}
