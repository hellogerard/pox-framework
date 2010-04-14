<?php

class Post_Comment extends BusinessObject
{
    private $_id;

    public function __construct($id = null)
    {
        // instantiating this object does not load any data
        $this->_id = $id;
    }

    public function load()
    {
        $this->_logger()->debug(__CLASS__ . '::' . __METHOD__);

        $sql = "select * from comments where comment_id = ? limit 1";

        $data = $this->_db()->getRows($sql, array($this->_id));
        return $data[0];
    }

    // since getTime() requires special logic, we can override this behavior here
    public function getTime()
    {
        return strtotime($this->_data['create_dt_tm']);
    }

    public function save()
    {
        $this->_logger()->debug(__CLASS__ . '::' . __METHOD__);

        $sql = "insert into comments
                    (comment_id, post_id, name, comment, create_dt_tm)
                    values
                    (null, ?, ?, ?, ?)";

        $bind = array($this->_data['post_id'],
                      $this->_data['name'],
                      $this->_data['comment'], 
                      $this->_data['create_dt_tm']);

        $this->_db()->query($sql, $bind);
    }
}

