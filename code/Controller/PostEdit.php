<?php

class Controller_PostEdit extends Controller_Abstract
{
    public function get($postId)
    {
        $post = $this->_getContainer()->Post()->load($postId);
        $tags = $this->_getContainer()->Tag()->fetchAll();

        echo $this->_getTwig()->render('post_edit.html.twig', array(
            'session'       => $this->_getSession(),
            'post'          => $post,
            'local_config'  => $this->_getContainer()->LocalConfig(),
            'tags'          => $tags,
        ));
    }

    public function post($postId)
    {
        $imageUrl = isset($_POST['image_url']) ? $_POST['image_url'] : null;
        $subject = isset($_POST['subject']) ? $_POST['subject'] : null;
        $body = isset($_POST['body']) ? $_POST['body'] : null;
        $tagIds = isset($_POST['tag_ids']) ? $_POST['tag_ids'] : null;
        $isActive = isset($_POST['is_active']) ? $_POST['is_active'] : null;

        if ($imageUrl) {
            if (strpos($imageUrl, "javascript:") !== false || strpos($imageUrl, "data:") !== false) {
                die("Looks like an injection attempt");
            }
        }

        if (! $tagIds || empty($tagIds)) {
            die("You have to pick at least one tag");
        }

        $post = $this->_getContainer()->Post()->load($postId);
        $post->set('subject', $subject)
            ->set('body', $body)
            ->set('tag_ids', $tagIds)
            ->set('name', isset($profileData['name']) ? $profileData['name'] : null)
            ->set('is_active', (int)$isActive)
            ->set('image_url', $imageUrl)
            ->save();

        header("location: /posts/" . $postId);
    }

}