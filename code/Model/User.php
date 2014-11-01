<?php

class Model_User extends Model_Record
{
    protected $_data;
    protected $_lastPost;

    protected function _getTable() { return 'users'; }
    protected function _getTableIdFieldname() { return 'user_id'; }
    protected function _getColumns()
    {
        return array('is_active', 'username', 'name', 'details_json');
    }

    /**
     * @var Model_LocalConfig
     */
    protected $_localConfig;

    public function __construct(Model_LocalConfig $config)
    {
        $this->_localConfig = $config;
    }

    public function loadByUsername($username)
    {
        $query = $this->_localConfig->database()->select()
            ->from("users")
            ->joinLeft(
                'user_vote',
                'user_vote.elected_user_id = users.user_id',
                array(
                    'COUNT(user_vote.user_vote_id) as vote_count'
                )
            )
            ->joinLeft(
                array('voting_user' => 'users'),
                'voting_user.user_id = user_vote.voting_user_id',
                array(
                    'GROUP_CONCAT(voting_user.name) as voting_users'
                )
            )
            ->group('users.user_id')
            //->where('users.is_active = 1')
            ->where("users.username = ?", $username);

        $this->_data = $this->_localConfig->database()->fetchRow($query);
        return $this;
    }

    public function hasVotedFor($electedUserId)
    {
        $query = $this->_localConfig->database()->select()
            ->from("user_vote")
            ->where("voting_user_id = ?", $this->getId())
            ->where("elected_user_id = ?", $electedUserId);

        return $this->_localConfig->database()->fetchOne($query);
    }

    public function addVoteFrom($votingUserId)
    {
        $this->_localConfig->database()->insert('user_vote', array(
            'voting_user_id'    => $votingUserId,
            'elected_user_id'   => $this->getId(),
            'created_at'        => \Carbon\Carbon::now()->toDateTimeString(),
        ));

        $data = array(
            'updated_at'    => \Carbon\Carbon::now()->toDateTimeString(),
        );
        $this->_localConfig->database()->update('users', $data, 'user_id = ' . $this->getId());

        return $this;
    }

    public function removeVoteFrom($votingUserId)
    {
        $this->_localConfig->database()->delete('user_vote',
            "voting_user_id = $votingUserId AND elected_user_id = " . $this->getId()
        );

        return $this;
    }

    public function load($userId)
    {
        $query = $this->_localConfig->database()->select()
            ->from("users")
            ->joinLeft(
                'user_vote',
                'user_vote.elected_user_id = users.user_id',
                array(
                    'COUNT(user_vote.user_vote_id) as vote_count'
                )
            )
            ->joinLeft(
                array('voting_user' => 'users'),
                'voting_user.user_id = user_vote.voting_user_id',
                array(
                    'GROUP_CONCAT(voting_user.name) as voting_users'
                )
            )
            ->group('users.user_id')
            //->where('users.is_active = 1')
            ->where("users.user_id = ?", $userId);

        $this->_data = $this->_localConfig->database()->fetchRow($query);
        return $this;
    }

    public function selectAll($activeOnly = true)
    {
        $query = $this->_localConfig->database()->select()
            ->from("users")
            ->joinLeft(
                'user_vote',
                'user_vote.elected_user_id = users.user_id',
                array(
                    'COUNT(user_vote.user_vote_id) as vote_count',
                )
            )
            ->joinLeft(
                array('voting_user' => 'users'),
                'voting_user.user_id = user_vote.voting_user_id',
                array(
                    'GROUP_CONCAT(voting_user.name) as voting_users'
                )
            )
            ->group('users.user_id')
            ->order('updated_at DESC')
        ;

        if ($activeOnly) {
            $query->where('users.is_active = 1');
        }

        return $query;
    }

    public function getName() { return $this->get('name'); }
    public function getVoteCount() { return $this->get('vote_count'); }
    public function getUsername() { return $this->get('username'); }
    public function getVotingUsernames() { return $this->get('voting_users'); }

    public function getImageUrl() { return $this->getDetail('image_url'); }
    public function getNextAvailable() { return $this->getDetail('next_available'); }
    public function certificationBoardUrl() { return $this->getDetail('certification_board_url'); }
    public function getCertifiedDeveloperUrl() { return $this->getDetail('certified_developer_url'); }
    public function certifiedDeveloperPlusUrl() { return $this->getDetail('certified_developer_plus_url'); }
    public function certifiedSolutionSpecialistUrl() { return $this->getDetail('certified_solution_specialist_url'); }
    public function certifiedFrontendDeveloperUrl() { return $this->getDetail('certified_frontend_developer_url'); }
    public function stackoverflowUrl() { return $this->getDetail('stackoverflow_url'); }
    public function linkedinUrl() { return $this->getDetail('linkedin_url'); }
    public function getGithubUsername() { return $this->getDetail('github_username'); }
    public function getTwitterUsername() { return $this->getDetail('twitter_username'); }
    public function getWebsiteUrl() { return $this->getDetail('url_website'); }
    public function getCompany() { return $this->getDetail('company'); }
    public function getAboutYou() { return $this->getDetail('about_you'); }

    public function getNextAvailableFriendly()
    {
       try {
            $dt = \Carbon\Carbon::parse($this->getDetail('next_available'));
            if ($dt->lt(\Carbon\Carbon::now())){
                return "Available";
            }else{
                return $dt->diffForHumans();
            }
       } catch (Exception $e) {
           return $this->getDetail('next_available');
       }
    }

    public function getDetail($key)
    {
        $detailJson = $this->get('details_json');
        $detailsArray = json_decode($detailJson, true);
        if (! $detailsArray) {
            throw new Exception("Problem decoding jsonfor user: " . $this->getId());
        }

        return isset($detailsArray[$key]) ? $detailsArray[$key] : null;
    }

    public function getLocation()
    {
        $parts = array();
        if ($this->getDetail('city')) {
            $parts[] = $this->getDetail('city');
        }

        if ($this->getDetail('state')) {
            $parts[] = $this->getDetail('state');
        }

        if ($this->getDetail('country')) {
            $parts[] = $this->getDetail('country');
        }

        return implode(", ", $parts);
    }

    // TODO: Make this more dry by using a beforecreate hook or something
    public function create()
    {
        foreach ($this->_getColumns() as $column) {
            $data[$column] = $this->get($column);
        }

        $data['updated_at'] = \Carbon\Carbon::now()->toDateTimeString();
        $data['is_active'] = true;
        $this->_localConfig->database()->insert($this->_getTable(), $data);

        return $this;
    }

    public function fetchPostCount()
    {
        $query = $this->_localConfig->database()->select()
            ->from('posts', array(
                'post_count' => 'COUNT(*)'
            ))
            ->where('posts.is_active = 1')
            ->where('user_id = ?', $this->getId());

        $postCount = $this->_localConfig->database()->fetchOne($query);
        return $postCount;
    }

    public function getLastPost()
    {
        if (isset($this->_lastPost)) {
            return $this->_lastPost;
        }

        $query = $this->_localConfig->database()->select()
            ->from('posts')
            ->where('user_id = ?', $this->getId())
            ->where('posts.is_active = 1')
            ->order('posts.post_id DESC');

        $row = $this->_localConfig->database()->fetchRow($query);
        if (! $row) {
            return null;
        }

        $postModel = $this->_getContainer()->Post()->setData($row);

        return $postModel;
    }
}