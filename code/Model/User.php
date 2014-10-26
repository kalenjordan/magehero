<?php

class Model_User
{
    protected $_data;

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
            ->group('users.user_id')
            ->where("username = ?", $username);

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
                    'COUNT(user_vote.user_vote_id) as vote_count'
                )
            )
            ->group('users.user_id')
            ->order('COUNT(user_vote.user_vote_id) DESC');

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
        $data = $this->_data;
        $data['updated_at'] = \Carbon\Carbon::now()->toDateTimeString();
        $this->_localConfig->database()->update('users', $data, 'user_id = ' . $this->getId());

        return $this;
    }

    public function create()
    {
        $data = $this->_data;
        $data['created_at'] = \Carbon\Carbon::now()->toDateTimeString();
        $this->_localConfig->database()->insert('users', $data);

        return $this;
    }

    public function getId() { return $this->get('user_id'); }
    public function getName() { return $this->get('name'); }
    public function getVoteCount() { return $this->get('vote_count'); }
    public function getUsername() { return $this->get('username'); }

    public function getImageUrl() { return $this->getDetail('image_url'); }
    public function getNextAvailable() { return $this->getDetail('next_available'); }
    public function certificationBoardUrl() { return $this->getDetail('certification_board_url'); }
    public function getCertifiedDeveloperUrl() { return $this->getDetail('certified_developer_url'); }
    public function certifiedDeveloperPlusUrl() { return $this->getDetail('certified_developer_plus_url'); }
    public function certifiedSolutionSpecialistUrl() { return $this->getDetail('certified_solution_specialist_url'); }
    public function certifiedFrontendDeveloperUrl() { return $this->getDetail('certified_frontend_developer_url'); }
    public function getGithubUsername() { return $this->getDetail('github_username'); }
    public function getTwitterUsername() { return $this->getDetail('twitter_username'); }
    public function getWebsiteUrl() { return $this->getDetail('url_website'); }
    public function getCompany() { return $this->getDetail('company'); }

    public function getLastUpdatedFriendly()
    {
        // return $this->get('last_updated');
        return \Carbon\Carbon::parse($this->get('updated_at'))->diffForHumans();
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
            throw new Exception("Problem decoding json");
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