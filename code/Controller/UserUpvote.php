<?php

class Controller_UserUpvote extends Controller_Abstract
{
    public function get($userId)
    {
        try {
            $this->_get($userId);
        } catch (Exception $e) {
            return $this->_jsonResponse(array(
                'success' => false,
                'message' => $e->getMessage(),
            ));
        }

        return $this;
    }

    protected function _get($userId)
    {
        if (! $this->_getUsername()) {
            throw new Exception("You have to be logged in to vote.");
        }

        $votingUser = $this->_getContainer()->User()->loadByUsername($this->_getUsername());
        if ($votingUser->getUsername() != 'kalenjordan' && $votingUser->getVoteCount() < 1) {
            throw new Exception("Sorry - you can't vote yet until you have at least one upvote yourself!");
        }

        $electedUser = $this->_getContainer()->User()->load($userId);

        if ($votingUser->getId() == $electedUser->getId()) {
            throw new Exception("Can't vote for your self.  Nice try, slick.");
        }

        if ($votingUser->hasVotedFor($electedUser->getId())) {
            $electedUser->removeVoteFrom($votingUser->getId());
        } else {
            $electedUser->addVoteFrom($votingUser->getId());
        }

        // Reload to get fresh vote count
        $electedUser = $this->_getContainer()->User()->load($userId);

        $this->_jsonResponse(array(
            'success' => true,
            'vote_count' => $electedUser->getVoteCount(),
        ));
    }
}