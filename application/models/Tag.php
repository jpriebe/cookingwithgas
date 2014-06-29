<?php
class Application_Model_Tag
{
    protected $_id = null;
    protected $_tag = '';

    public function get_id() { return $this->_id; }
    public function get_tag() { return $this->_tag; }

    public function __construct ($id, $tag)
    {
        $this->_id = $id;
        $this->_tag = $tag;
    }

}

