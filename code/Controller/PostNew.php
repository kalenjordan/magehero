<?php

class Controller_PostNew extends Controller_Abstract
{
    public function get()
    {
        $data = array(
            "subject"   => "New Post",
            "is_active" => true,
            "user_id"   => $this->_getCurrentUser()->getId(),
        );

        $post = $this->_getContainer()->Post()->setData($data)->save();
        $postId = $post->getId();

        header("location: /posts/$postId/edit");
        exit;
    }
}