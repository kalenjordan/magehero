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
    public function voteCount() { return $this->get('vote_count'); }
    public function getUpvotersCsv() { return $this->get('upvoters_csv'); }

    public function getCreatedAtFriendly()
    {
        try {
            return \Carbon\Carbon::parse($this->getCreatedAt())->diffForHumans();
        } catch (Exception $e) {
            return $this->getCreatedAt();
        }
    }

    /**
     * @return Model_User
     */
    public function getUser()
    {
        if (isset($this->_user)) {
            return $this->_user;
        }

        if ($this->getUserId()) {
            $this->_user = $this->_getContainer()->User()->load($this->getUserId());
        }
        return $this->_user;
    }

    public function fetchTags()
    {
        $tags = $this->_getContainer()->Tag()->fetchByPostId($this->getId());
        return $tags;
    }

    public function selectAll()
    {
        $table = $this->_getTable();

        $query = $this->_localConfig->database()->select()
            ->from($table)
            ->joinLeft(array('post_vote' => 'post_vote'),
                'post_vote.post_id = posts.post_id',
                array(
                    'vote_count' => 'COUNT(DISTINCT post_vote_id)',
                    'upvoters_csv' => 'GROUP_CONCAT(DISTINCT voting_user.username)'
                )
            )
            ->joinLeft(array('voting_user' => 'users'),
                'voting_user.user_id = post_vote.voting_user_id',
                array()
            )
            ->order(array("COUNT(DISTINCT post_vote_id) DESC", "posts.created_at DESC"))
            ->group('posts.post_id');

        return $query;
    }

    public function load($entityId)
    {
        $table = $this->_getTable();
        $tableIdFieldname = $this->_getTableIdFieldname();

        $query = $this->_localConfig->database()->select()
            ->from($table)
            ->joinLeft(array('post_vote' => 'post_vote'),
                'post_vote.post_id = posts.post_id',
                array(
                    'vote_count' => 'COUNT(DISTINCT post_vote_id)',
                )
            )
            ->group('posts.post_id')
            ->where("$table.$tableIdFieldname = ?", $entityId);

        $this->_data = $this->_localConfig->database()->fetchRow($query);
        return $this;
    }

    public function fetchAllRecent()
    {
        $recentTimePeriod = $this->_localConfig->getRecentTimePeriod();
        if (! $recentTimePeriod) {
            throw new Exception("Missing recent_time_period in config");
        }

        $query = $this->selectAll()
            ->where("posts.created_at > DATE_SUB(NOW(), INTERVAL $recentTimePeriod)")
            ->where('posts.is_active = 1');
        $results = $this->_localConfig->database()->fetchAll($query);

        return $results;
    }

    public function fetchAllWithAuthor()
    {
        $query = $this->selectAll()
            ->where('posts.is_active = 1')
            ->joinLeft(
                'users',
                "users.user_id = posts.user_id",
                array('name')
            );
        $rows = $this->_localConfig->database()->fetchAll($query);

        $models = array();
        foreach ($rows as $row) {
            $model = $this->_getContainer()->Post()->setData($row);
            $models[] = $model;
        }

        return $models;
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
        $url = implode("/", array($this->_localConfig->get('base_url'), "posts", $this->getId(), $this->getSlug()));
        return $url;
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

    public function getSlug()
    {
        $text = $this->getSubject();

        // replace non letter or digits by -
        $text = preg_replace('~[^\\pL\d]+~u', '-', $text);

        // trim
        $text = trim($text, '-');

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // lowercase
        $text = strtolower($text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        return $text;
    }

    protected function _afterSave()
    {
        // Update the updated_at timestamp
        $this->getUser()->save();
    }
}
