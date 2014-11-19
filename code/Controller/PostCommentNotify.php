<?php

class Controller_PostCommentNotify extends Controller_Abstract
{
    public function get($postId)
    {
        // Disabled b/c bad notifications are going out
        $this->_jsonResponse(array(
            'success'   => false,
            'message'   => "Disabled",
        ));
        return $this;

        $post = $this->_getContainer()->Post()->load($postId);
        $user = $post->getUser();
        if (! $user->getEmail()) {
            $this->_jsonResponse(array(
                'success'   => false,
                'message'   => "Can't notify because they don't have an email (user ID: " . $user->getId() . ")",
            ));
            return $this;
        }

        mail($user->getEmail(), "MageHero Comment: " . $post->getSubject(), "See comment here:\r\n" . $post->getUrl(), "From: comments@magehero.com");

        $this->_jsonResponse(array(
            'success' => true,
            'message'   => "Notified " . $user->getName(),
        ));

        return $this;
    }
}