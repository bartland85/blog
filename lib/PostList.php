<?php
/**
 * Created by PhpStorm.
 * User: Bart
 * Date: 2015-04-19
 * Time: 11:57
 */

namespace Bart;

use Config\DB;

class PostList {

    private $posts;
    private $postCount;
    private $listLimit;

    function __construct($postList = array())
    {
        $this->posts = $postList;
        $this->postCount = count($postList);
        $this->listLimit = 5;
    }

    /**
     * @return mixed
     */
    public function getPosts()
    {
        return $this->posts;
    }

    /**
     * @param mixed $posts
     */
    public function setPosts($posts)
    {
        $this->posts = $posts;
    }

    /**
     * @return mixed
     */
    public function getPostCount()
    {
        return $this->postCount;
    }

    /**
     * @param mixed $postCount
     */
    public function setPostCount($postCount)
    {
        $this->postCount = $postCount;
    }

    /**
     * @return mixed
     */
    public function getListLimit()
    {
        return $this->listLimit;
    }

    /**
     * @param mixed $listLimit
     */
    public function setListLimit($listLimit)
    {
        $this->listLimit = $listLimit;
    }

    public function getNewest2Limit(){

        $db = new DB();

        $data = $db->query('select * from posts ORDER BY datetime DESC limit :limit', array('limit'=>$this->listLimit));

        return $data;
    }




}