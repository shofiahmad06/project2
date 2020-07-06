<?php

namespace Dompdf\Frame;

use DOMDocument;
use DOMNode;
use DOMElement;
use DOMXPath;

use Dompdf\Exception;
use Dompdf\Frame;


class FrameTree
{
   
    protected static $HIDDEN_TAGS = array(
        "area",
        "base",
        "basefont",
        "head",
        "style",
        "meta",
        "title",
        "colgroup",
        "noembed",
        "param",
        "#comment"
    );


    protected $_dom;

 
    protected $_root;

    protected $_absolute_frames;

    protected $_registry;


    public function __construct(DomDocument $dom)
    {
        $this->_dom = $dom;
        $this->_root = null;
        $this->_registry = array();
    }

   
    public function get_dom()
    {
        return $this->_dom;
    }


    public function get_root()
    {
        return $this->_root;
    }


    public function get_frame($id)
    {
        return isset($this->_registry[$id]) ? $this->_registry[$id] : null;
    }

 
    public function get_frames()
    {
        return new FrameTreeList($this->_root);
    }


    public function build_tree()
    {
        $html = $this->_dom->getElementsByTagName("html")->item(0);
        if (is_null($html)) {
            $html = $this->_dom->firstChild;
        }

        if (is_null($html)) {
            throw new Exception("Requested HTML document contains no data.");
        }

        $this->fix_tables();

        $this->_root = $this->_build_tree_r($html);
    }

    /**
     * Adds missing TBODYs around TR
     */
    protected function fix_tables()
    {
        $xp = new DOMXPath($this->_dom);

        // Move table caption before the table
        // FIXME find a better way to deal with it...
        $captions = $xp->query('//table/caption');
        foreach ($captions as $caption) {
            $table = $caption->parentNode;
            $table->parentNode->insertBefore($caption, $table);
        }

        $firstRows = $xp->query('//table/tr[1]');
        /** @var DOMElement $tableChild */
        foreach ($firstRows as $tableChild) {
            $tbody = $this->_dom->createElement('tbody');
            $tableNode = $tableChild->parentNode;
            do {
                if ($tableChild->nodeName === 'tr') {
                    $tmpNode = $tableChild;
                    $tableChild = $tableChild->nextSibling;
                    $tableNode->removeChild($tmpNode);
                    $tbody->appendChild($tmpNode);
                } else {
                    if ($tbody->hasChildNodes() === true) {
                        $tableNode->insertBefore($tbody, $tableChild);
                        $tbody = $this->_dom->createElement('tbody');
                    }
                    $tableChild = $tableChild->nextSibling;
                }
            } while ($tableChild);
            if ($tbody->hasChildNodes() === true) {
                $tableNode->appendChild($tbody);
            }
        }
    }


    protected function _remove_node(DOMNode $node, array &$children, $index)
    {
        $child = $children[$index];
        $previousChild = $child->previousSibling;
        $nextChild = $child->nextSibling;
        $node->removeChild($child);
        if (isset($previousChild, $nextChild)) {
            if ($previousChild->nodeName === "#text" && $nextChild->nodeName === "#text")
            {
                $previousChild->nodeValue .= $nextChild->nodeValue;
                $this->_remove_node($node, $children, $index+1);
            }
        }
        array_splice($children, $index, 1);
    }


    protected function _build_tree_r(DOMNode $node)
    {
        $frame = new Frame($node);
        $id = $frame->get_id();
        $this->_registry[$id] = $frame;

        if (!$node->hasChildNodes()) {
            return $frame;
        }

        // Store the children in an array so that the tree can be modified
        $children = array();
        $length = $node->childNodes->length;
        for ($i = 0; $i < $length; $i++) {
            $children[] = $node->childNodes->item($i);
        }
        $index = 0;
        // INFO: We don't advance $index if a node is removed to avoid skipping nodes
        while ($index < count($children)) {
            $child = $children[$index];
            $nodeName = strtolower($child->nodeName);

            // Skip non-displaying nodes
            if (in_array($nodeName, self::$HIDDEN_TAGS)) {
                if ($nodeName !== "head" && $nodeName !== "style") {
                    $this->_remove_node($node, $children, $index);
                } else {
                    $index++;
                }
                continue;
            }
            // Skip empty text nodes
            if ($nodeName === "#text" && $child->nodeValue === "") {
                $this->_remove_node($node, $children, $index);
                continue;
            }
            // Skip empty image nodes
            if ($nodeName === "img" && $child->getAttribute("src") === "") {
                $this->_remove_node($node, $children, $index);
                continue;
            }

            if (is_object($child)) {
                $frame->append_child($this->_build_tree_r($child), false);
            }
            $index++;
        }

        return $frame;
    }

    public function insert_node(DOMElement $node, DOMElement $new_node, $pos)
    {
        if ($pos === "after" || !$node->firstChild) {
            $node->appendChild($new_node);
        } else {
            $node->insertBefore($new_node, $node->firstChild);
        }

        $this->_build_tree_r($new_node);

        $frame_id = $new_node->getAttribute("frame_id");
        $frame = $this->get_frame($frame_id);

        $parent_id = $node->getAttribute("frame_id");
        $parent = $this->get_frame($parent_id);

        if ($parent) {
            if ($pos === "before") {
                $parent->prepend_child($frame, false);
            } else {
                $parent->append_child($frame, false);
            }
        }

        return $frame_id;
    }
}
