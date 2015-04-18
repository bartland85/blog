<?php
/**
 * Created by PhpStorm.
 * User: Bart
 * Date: 2015-04-18
 * Time: 23:00
 */


namespace Bart;



class Post {


    private $id;
    private $title;
    private $text;
    private $user_id;
    private $datetime;

    function __construct($id=null, $title=null, $text=null, $user_id=null, $datetime=null)
    {
        $this->id = $id;
        $this->title = $title;
        $this->text = $text;
        $this->user_id = $user_id;
        $this->datetime = $datetime;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }
    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param mixed $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * @return mixed
     */
    public function getUserid()
    {
        return $this->user_id;
    }

    /**
     * @param mixed $user_id
     */
    public function setUserid($user_id)
    {
        $this->user_id = $user_id;
    }

    /**
     * @return mixed
     */
    public function getDatetime()
    {
        return $this->datetime;
    }

    /**
     * @param mixed $datetime
     */
    public function setDatetime($datetime)
    {
        $this->datetime = $datetime;
    }

    public function getById($id){

        $db = new \Config\DB();

        $db->connect();

        return $db->query('select * from posts where id=:id limit 1', array('id'=>$id));

    }

}