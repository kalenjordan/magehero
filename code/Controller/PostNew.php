<?php

class Controller_PostNew extends Controller_Abstract
{
    public function get()
    {
        $minimumVoteCount = $this->_getContainer()->LocalConfig()->getPostingMinimumVotecount();
        if ($this->_getCurrentUser()->getVoteCount() < $minimumVoteCount) {
            die("You have to have $minimumVoteCount vote(s) in order to post");
        }

        $data = array(
            "subject"   => "New Post",
            "is_active" => false,
            "user_id"   => $this->_getCurrentUser()->getId(),
        );

        $post = $this->_getContainer()->Post()->setData($data)->save();
        $postId = $post->getId();

        header("location: /posts/$postId/edit");
        exit;
    }
}