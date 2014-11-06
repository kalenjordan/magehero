<?php

namespace Model;

use \Model\LocalConfig;
use \DI\ContainerBuilder;

class Container extends \DI\Container
{
    protected $_container;

    public function __construct()
    {
        $builder = new ContainerBuilder();
        $container = $builder->build();
        //Register Notification Implementation
        $container->set('Services_NotifyInterface', \DI\object('Services_TwitterNotify'));
        $this->_container = $container;

        return $container;
    }

    /**
     * @return \Model\User
     */
    public function User()
    {
        return $this->_container->make('\Model\User');
    }

    /**
     * @return \Model\Tag
     */
    public function Tag()
    {
        return $this->_container->make('\Model\Tag');
    }

    /**
     * @return \Model\PostTag
     */
    public function PostTag()
    {
        return $this->_container->make('\Model\PostTag');
    }

    /**
     * @return \Model\Post
     */
    public function Post()
    {
        return $this->_container->make('\Model\Post');
    }

    public function LocalConfig()
    {
        return new LocalConfig();
    }

    public function notify()
    {
        return $this->_container->get('Services_NotifyInterface');
    }
}