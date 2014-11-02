<?php

abstract class Model_Record
{
    protected $_data;
    abstract protected function _getTable();
    abstract protected function _getTableIdFieldname();
    abstract protected function _getColumns();

    /**
     * @var Model_LocalConfig
     */
    protected $_localConfig;

    public function __construct(Model_LocalConfig $config)
    {
        $this->_localConfig = $config;
    }

    public function load($entityId)
    {
        $table = $this->_getTable();
        $tableIdFieldname = $this->_getTableIdFieldname();

        $query = $this->_localConfig->database()->select()
            ->from($table)
            ->where("$table.$tableIdFieldname = ?", $entityId);

        $this->_data = $this->_localConfig->database()->fetchRow($query);
        return $this;
    }

    public function selectAll()
    {
        $table = $this->_getTable();
        $tableIdFieldname = $this->_getTableIdFieldname();

        $query = $this->_localConfig->database()->select()
            ->from($table)
            ->order("$tableIdFieldname DESC");

        return $query;
    }

    public function fetchAll()
    {
        $results = $this->_localConfig->database()->fetchAll($this->selectAll());
        return $results;
    }

    public function setData($data)
    {
        $this->_data = $data;
        return $this;
    }

    public function set($key, $val)
    {
        $this->_data[$key] = $val;
        return $this;
    }

    public function save()
    {
        $tableIdFieldname = $this->_getTableIdFieldname();

        if ($this->get($tableIdFieldname)) {
            $this->update();
        } else {
            $this->create();
        }

        $this->_afterSave();

        return $this;
    }

    // Can be overridden by children
    protected function _afterSave() { }

    public function update()
    {
        $table = $this->_getTable();
        $tableIdFieldname = $this->_getTableIdFieldname();

        foreach ($this->_getColumns() as $column) {
            $data[$column] = $this->get($column);
        }
        $data['updated_at'] = \Carbon\Carbon::now()->toDateTimeString();

        $this->_localConfig->database()->update($table, $data, "$tableIdFieldname = " . $this->getId());

        return $this;
    }

    public function create()
    {
        foreach ($this->_getColumns() as $column) {
            $data[$column] = $this->get($column);
        }

        $data['created_at'] = \Carbon\Carbon::now()->toDateTimeString();
        $data['updated_at'] = \Carbon\Carbon::now()->toDateTimeString();
        $this->_localConfig->database()->insert($this->_getTable(), $data);

        // This is probably going to cause horrible bugs.  #rollingyourownormproblems
        $recordId = $this->_localConfig->database()->lastInsertId();
        $this->set($this->_getTableIdFieldname(), $recordId);

        return $this;
    }

    public function getId() {
        return $this->get($this->_getTableIdFieldname());
    }

    public function getLastUpdatedFriendly()
    {
        try {
            return \Carbon\Carbon::parse($this->get('updated_at'))->diffForHumans();
        } catch (Exception $e) {
            return $this->get('updated_at');
        }
    }

    public function get($key)
    {
        return isset($this->_data[$key]) ? $this->_data[$key] : null;
    }

    protected function _getContainer()
    {
        if (isset($this->_container)) {
            return $this->_container;
        }

        $container = new Model_Container();

        $this->_container = $container;
        return $this->_container;
    }
}