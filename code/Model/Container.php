<?php

class Model_Container extends DI\Container
{
    protected $_container;

    public function __construct()
    {
        $builder = new \DI\ContainerBuilder();
        $container = $builder->build();
        $this->_container = $container;

        return $container;
    }

    /**
     * @return Model_User
     */
    public function User()
    {
        return $this->_container->get('Model_User');
    }
}