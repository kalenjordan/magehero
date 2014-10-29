<?php

class Model_User
{
    protected $_data;

    protected $_validationPatterns = [
        'image_url'                         => '|^https?://|',
        'certification_board_url'           => '|^https?://|',
        'certified_developer_url'           => '|^https?://www\.magentocommerce\.com/certification/directory/dev/|',
        'certified_developer_plus_url'      => '|^https?://www\.magentocommerce\.com/certification/directory/dev/|',
        'certified_solution_specialist_url' => '|^https?://www\.magentocommerce\.com/certification/directory/dev/|',
        'certified_frontend_developer_url'  => '|^https?://www\.magentocommerce\.com/certification/directory/dev/|',
        'stackoverflow_url'                 => '|^https?://stackoverflow\.com/users/|',
    ];

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
            ->where('users.is_active = 1')
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
            ->group('users.user_id')
            ->where('users.is_active = 1')
            ->where("users.user_id = ?", $userId);

        $this->_data = $this->_localConfig->database()->fetchRow($query);
        return $this;
    }

    public function fetchAll()
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
            ->where('users.is_active = 1')
            ->group('users.user_id')
            ->order(new Zend_Db_Expr('IF(COUNT(user_vote.user_vote_id) >= 4, 1, IF(COUNT(user_vote.user_vote_id) >= 1, 2, 3)) ASC, updated_at DESC'));

        $results = $this->_localConfig->database()->fetchAll($query);
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
        if ($this->get('user_id')) {
            $this->update();
        } else {
            $this->create();
        }

        return $this;
    }

    public function update()
    {
        $data = array(
            'details_json'  => $this->validateDetails($this->_data['details_json']),
            'updated_at'    => \Carbon\Carbon::now()->toDateTimeString(),
            'username'      => $this->_data['username'],
            'name'          => $this->_data['name'],
        );
        $this->_localConfig->database()->update('users', $data, 'user_id = ' . $this->getId());

        return $this;
    }

    protected function validateDetails($json)
    {
        $data = json_decode($json);
        foreach ($this->_validationPatterns as $key => $pattern) {
            if (!isset($data[$key])) {
                continue;
            }
            $preg_result = preg_match($pattern, $data[$key]);
            if ($preg_result !== 1) {
                throw new Exception("JSON failed validation at $key.");
            }
        }
        return $json;
    }

    public function create()
    {
        $data = array(
            'details_json' => $this->_data['details_json'],
            'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
            'updated_at'    => \Carbon\Carbon::now()->toDateTimeString(),
            'username'      => $this->_data['username'],
            'name'          => $this->_data['name'],
        );
        $this->_localConfig->database()->insert('users', $data);

        return $this;
    }

    public function getId() { return $this->get('user_id'); }
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
    public function getGithubUsername() { return $this->getDetail('github_username'); }
    public function getTwitterUsername() { return $this->getDetail('twitter_username'); }
    public function getWebsiteUrl() { return $this->getDetail('url_website'); }
    public function getCompany() { return $this->getDetail('company'); }
    public function getAboutYou() { return $this->getDetail('about_you'); }

    public function getLastUpdatedFriendly()
    {
        try {
            return \Carbon\Carbon::parse($this->get('updated_at'))->diffForHumans();
        } catch (Exception $e) {
            return $this->get('updated_at');
        }
    }

    public function getNextAvailableFriendly()
    {
       try {
           return \Carbon\Carbon::parse($this->getDetail('next_available'))->diffForHumans();
       } catch (Exception $e) {
           return $this->getDetail('next_available');
       }
    }

    public function get($key)
    {
        return isset($this->_data[$key]) ? $this->_data[$key] : null;
    }

    public function getDetail($key)
    {
        $detailJson = $this->get('details_json');
        $detailsArray = json_decode($detailJson, true);
        if (! $detailsArray) {
            throw new Exception("Problem decoding json for user: " . $this->getId());
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
}
