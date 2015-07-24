<?php

class Model_Post extends Model_Record
{
    protected $_data;
    protected $_user;

    protected function _getTable() { return 'posts'; }
    protected function _getTableIdFieldname() { return 'post_id'; }
    protected function _getColumns()
    {
        return array('user_id', 'is_active', 'is_news', 'image_url', 'subject', 'body');
    }

    /**
     * @var Model_LocalConfig
     */
    protected $_localConfig;

    public function __construct(Model_LocalConfig $config)
    {
        $this->_localConfig = $config;
    }

    public function getSubject() { return $this->get('subject'); }
    public function getBody()    { return $this->get('body'); }

    public function getBodyAsHtml()
    {
        $parseDown = new Parsedown();
        $purifier = new HTMLPurifier(HTMLPurifier_Config::createDefault());
        $body = $parseDown->text($this->get('body'));
        $body = $purifier->purify($body);

        return $body;
    }

    public function getUserId()      { return $this->get('user_id'); }
    public function getImageUrl()    { return $this->get('image_url'); }
    public function getIsActive()    { return $this->get('is_active'); }
    public function getCreatedAt()   { return $this->get('created_at'); }
    public function getUpdatedAt()   { return $this->get('updated_at'); }
    public function voteCount()      { return $this->get('vote_count'); }
    public function getUpvotersCsv() { return $this->get('upvoters_csv'); }
    public function isNews()         { return $this->get('is_news'); }
    public function isRecent()       { return $this->get('is_recent'); }

    /**
     * @return \Carbon\Carbon
     */
    public function getCreatedAtDate()
    {
        try {
            return \Carbon\Carbon::parse($this->getCreatedAt());
        } catch (Exception $e) {
            return null;
        }
    }

    public function getCreatedAtFriendly()
    {
        $createdAtDate = $this->getCreatedAtDate();
        if (! $createdAtDate) {
            return $this->getCreatedAt();
        }

        return $createdAtDate->diffForHumans();
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
                "post_vote.post_id = $table.post_id",
                array(
                    'vote_count' => 'COUNT(DISTINCT post_vote_id)',
                    'upvoters_csv' => 'GROUP_CONCAT(DISTINCT voting_user.username)'
                )
            )
            ->joinLeft(array('voting_user' => 'users'),
                'voting_user.user_id = post_vote.voting_user_id',
                array()
            )
            ->order(array("COUNT(DISTINCT post_vote_id) DESC", $table . ".created_at DESC"))
            ->group($table . '.post_id');

        return $query;
    }

    public function load($entityId)
    {
        $table = $this->_getTable();
        $tableIdFieldname = $this->_getTableIdFieldname();

        $query = $this->_localConfig->database()->select()
            ->from($table)
            ->joinLeft(array('post_vote' => 'post_vote'),
                "post_vote.post_id = $table.post_id",
                array(
                    'vote_count' => 'COUNT(DISTINCT post_vote_id)',
                )
            )
            ->joinLeft(array('voting_user' => 'users'),
                'voting_user.user_id = post_vote.voting_user_id',
                array(
                    'upvoters_csv' => 'GROUP_CONCAT(DISTINCT voting_user.username)'
                )
            )
            ->group("$table.post_id")
            ->where("$table.$tableIdFieldname = ?", $entityId);

        $this->_data = $this->_localConfig->database()->fetchRow($query);
        return $this;
    }

    public function fetchAllRecent()
    {
        $table = $this->_getTable();

        $recentTimePeriod = $this->_localConfig->getRecentTimePeriod();
        if (! $recentTimePeriod) {
            throw new Exception("Missing recent_time_period in config");
        }

        $query = $this->selectAll()
            ->where("$table.created_at > DATE_SUB(NOW(), INTERVAL $recentTimePeriod)")
            ->where("$table.is_active = 1");
        $results = $this->_localConfig->database()->fetchAll($query);

        return $results;
    }

    public function fetchAllWithAuthor()
    {
        $table = $this->_getTable();

        $query = $this->selectAll()
            ->where("$table.is_active = 1")
            ->joinLeft(
                'users',
                "users.user_id = $table.user_id",
                array('name')
            )
            ->limit(20);
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
        $table = $this->_getTable();

        $query = $this->selectAll();
        $query->where("$table.user_id = ?", $userId);
        $query->where("$table.is_active = 1", $userId);
        $rows = $this->_localConfig->database()->fetchAll($query);

        $models = array();
        foreach ($rows as $row) {
            $model = $this->_getContainer()->Post()->setData($row);
            $models[] = $model;
        }

        return $models;
    }

    /**
     * Search string within post title and body.
     *
     * Algorithm here is to count mentions of search terms in body and subject colums. Weighting subject by 5.
     * This method isn't known for being particularly performant on large databases but MVP and all that.
     *
     * select posts.subject, (
     *      (subject regexp '[[:<:]]Magento[[:>:]]' and body regexp '[[:<:]]Magento[[:>:]]') * 5
     *          +
     *      (subject regexp '[[:<:]]2[[:>:]]' and body regexp '[[:<:]]2[[:>:]]'))
     *          as hits
     * from posts
     * having hits > 0
     * order by hits desc;
     * @param $term
     *
     * @return array
     */
    public function fetchByTerm($term)
    {
        $terms = explode(" ", $term);

        $searchQuery = array();
        foreach ($terms as $term) {
            $term = $this->_localConfig->database()->quote("[[:<:]]" . $term . "[[:>:]]");
            $searchQuery[] = "(subject regexp $term) * 5 + (body regexp $term)";
        }

        $searchQuery = implode(" + ", $searchQuery);

        $query = $this->selectAll();
        $query->columns(new Zend_Db_Expr("($searchQuery) as hits"));
        $query->having('hits > 0');

        // We need to reset the ordering that was put on in selectAll()
        $query->reset( Zend_Db_Select::ORDER );
        $query->order("hits DESC");

        $rows = $this->_localConfig->database()->fetchAll($query);

        $models = array();
        foreach ($rows as $row) {
            $model = $this->_getContainer()->Post()->setData($row);
            $models[] = $model;
        }

        return $models;
    }

    public function fetchByTagId($tagId)
    {
        $table = $this->_getTable();

        $query = $this->selectAll()
            ->joinLeft(
                'post_tag',
                "post_tag.post_id = $table.post_id",
                array()
            )
            ->joinLeft(
                'tags',
                'post_tag.tag_id = tags.tag_id',
                array()
            )
            ->where("post_tag.tag_id = $tagId");

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
    
    public function getSluglessUrl()
    {
        return implode("/", array($this->_localConfig->get('base_url'), "posts", $this->getId()));
    }

    public function getEditUrl()
    {
        $url = implode("/", array($this->_localConfig->get('base_url'), "posts", $this->getId(), "edit"));
        return $url;
    }

    public function getTweetUrl()
    {
        $text = $this->getSubject() . " " . $this->getUrl();
        $tweetIntentUrl = "https://twitter.com/intent/tweet?text=" . urlencode($text);

        return $tweetIntentUrl;
    }

    public function getTweetPropsUrl()
    {
        $text = "Props to @" . $this->getUser()->getTwitterUsername() . " for " . $this->getUrl();
        $tweetIntentUrl = "https://twitter.com/intent/tweet?text=" . urlencode($text);

        return $tweetIntentUrl;
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

        // Add tags
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
    }

    public function isNew()
    {
        $createdAtDate = $this->getCreatedAtDate();
        if (! $createdAtDate) {
            return false;
        }

        return $createdAtDate->diffInHours() < $this->_localConfig->getNumberOfHoursForNew();
    }
}
