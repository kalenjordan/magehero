<?php

class Model_Container extends DI\Container
{
    protected $_container;

    public function __construct()
    {
        $builder = new \DI\ContainerBuilder();
        $container = $builder->build();
        //Register Notification Implementation
        $container->set('Services_NotifyInterface', \DI\object('Services_TwitterNotify'));
        $this->_container = $container;

        return $container;
    }

    /**
     * @return Model_User
     */
    public function User()
    {
        return $this->_container->make('Model_User');
    }

    public function notify()
    {
        return $this->_container->get('Services_NotifyInterface');
    }
}