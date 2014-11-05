<?php

class Model_Post extends Model_Record
{
    protected $_data;
    protected $_user;

    protected function _getTable() { return 'posts'; }
    protected function _getTableIdFieldname() { return 'post_id'; }
    protected function _getColumns()
    {
        return array('user_id', 'is_active', 'image_url', 'subject', 'body');
    }

    /**
     * @var Model_LocalConfig
     */
    protected $_localConfig;

    public function __construct(Model_LocalConfig $config)
    {
        $this->_localConfig = $config;
    }

    public function getSubject()    { return $this->get('subject'); }
    public function getBody()    { return $this->get('body'); }

    public function getBodyAsHtml()
    {
        $parseDown = new Parsedown();
        $purifier = new HTMLPurifier(HTMLPurifier_Config::createDefault());
        $body = $parseDown->text($this->get('body'));
        $body = $purifier->purify($body);

        return $body;
    }

    public function getUserId()     { return $this->get('user_id'); }
    public function getImageUrl()   { return $this->get('image_url'); }
    public function getIsActive()   { return $this->get('is_active'); }
    public function getCreatedAt()   { return $this->get('created_at'); }

    public function getUser()
    {
        if (isset($this->_user)) {
            return $this->_user;
        }

        $this->_user = $this->_getContainer()->User()->load($this->getUserId());
        return $this->_user;
    }

    public function fetchTags()
    {
        $tags = $this->_getContainer()->Tag()->fetchByPostId($this->getId());
        return $tags;
    }

    public function fetchByUserId($userId)
    {
        $query = $this->selectAll();
        $query->where('posts.user_id = ?', $userId);
        $query->where('posts.is_active = 1', $userId);
        $rows = $this->_localConfig->database()->fetchAll($query);

        $models = array();
        foreach ($rows as $row) {
            $model = $this->_getContainer()->Post()->setData($row);
            $models[] = $model;
        }

        return $models;
    }

    public function getUrl()
    {
        return $this->_localConfig->get('base_url') . "/posts/" . $this->getId();
    }

    public function getTweetUrl()
    {
        $text = $this->getSubject() . " " . $this->getUrl();

        $tweetIntentUrl = "https://twitter.com/intent/tweet?text=" . urlencode($text);
        return $tweetIntentUrl;
    }

    // todo: Should use a beforeUpdate hook or something for this.
    public function update()
    {
        $table = $this->_getTable();
        $tableIdFieldname = $this->_getTableIdFieldname();

        foreach ($this->_getColumns() as $column) {
            $data[$column] = $this->get($column);
        }
        $data['updated_at'] = \Carbon\Carbon::now()->toDateTimeString();

        $this->_localConfig->database()->update($table, $data, "$tableIdFieldname = " . $this->getId());

        if ($this->get('tag_ids') && is_array($this->get('tag_ids'))) {
            $this->_localConfig->database()->delete("post_tag", "post_id = " . $this->getId());
            foreach ($this->get('tag_ids') as $tagId) {
                $tagPostRelationship = $this->_getContainer()->PostTag()->setData(array(
                    'post_id' => $this->getId(),
                    'user_id' => $this->getUserId(),
                    'tag_id' => $tagId
                ));
                $tagPostRelationship->save();
            }

        }

        return $this;
    }

    public function hasTagId($tagId)
    {
        $tags = $this->fetchTags();

        /** @var $tag Model_Tag */
        foreach ($tags as $tag) {
            if ($tag->getId() == $tagId) {
                return true;
            }
        }

        return false;
    }

    protected function _afterSave()
    {
        // Update the updated_at timestamp
        $this->getUser()->save();
    }
}
