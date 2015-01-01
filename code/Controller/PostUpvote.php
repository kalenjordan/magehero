<?php

class Controller_PostUpvote extends Controller_Abstract
{
    public function get($postId)
    {
        try {
            $this->_get($postId);
        } catch (Exception $e) {
            return $this->_jsonResponse(array(
                'success' => false,
                'message' => $e->getMessage(),
            ));
        }

        return $this;
    }

    protected function _get($postId)
    {
        if (! $this->_getUsername()) {
            throw new Exception("You have to be logged in to vote.");
        }

        $votingUser = $this->_getCurrentUser();
        $post = $this->_getContainer()->Post()->load($postId);

        if ($votingUser->getId() == $post->getUserId()) {
            throw new Exception("Can't upvote your own post, slick.  HOW DARE YOU.");
        }

        if ($votingUser->hasVotedForPost($postId)) {
            $votingUser->removeVoteFromPost($postId);
        } else {
            $votingUser->addVoteToPost($postId);
        }

        // Reload to get fresh vote count
        $post = $this->_getContainer()->Post()->load($postId);

        $this->_jsonResponse(array(
            'success' => true,
            'vote_count' => $post->voteCount(),
        ));
    }
}