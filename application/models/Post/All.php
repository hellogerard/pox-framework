<?php

class Post_All extends BusinessObject
{
    public function getPosts()
    {
        $sql =  "select post_id from posts";
        $this->_hint('Post');
        return $this->_db()->getCol($sql);
    }
}

