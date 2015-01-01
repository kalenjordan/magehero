<?php


use Imgur\Client;

class Controller_PostEdit extends Controller_Abstract
{
    public function get($postId)
    {
        $post = $this->_getContainer()->Post()->load($postId);
        $tags = $this->_getContainer()->Tag()->fetchAll();

        if ($this->_getCurrentUser()->getId() != $post->getUserId()) {
            die("Permission denied");
        }

        echo $this->_getTwig()->render('post_edit.html.twig', array(
            'session'       => $this->_getSession(),
            'post'          => $post,
            'local_config'  => $this->_getContainer()->LocalConfig(),
            'tags'          => $tags,
        ));
    }

    public function post($postId)
    {
        $imageUrl = ($_FILES['image_url']['size'] > 0) ? $_FILES['image_url'] : null;
        $subject = isset($_POST['subject']) ? $_POST['subject'] : null;
        $body = isset($_POST['body']) ? $_POST['body'] : null;
        $tagIds = isset($_POST['tag_ids']) ? $_POST['tag_ids'] : null;
        $isActive = isset($_POST['is_active']) ? $_POST['is_active'] : null;


        if (! $tagIds || empty($tagIds)) {
            die("You have to pick at least one tag");
        }

        $post = $this->_getContainer()->Post()->load($postId);
        if ($this->_getCurrentUser()->getId() != $post->getUserId()) {
            die("Permission denied");
        }

        if ($imageUrl) {
            $fileName = $_FILES['image_url']['tmp_name'];
            $response = $this->uploadImage($fileName);
            $imageUrl = $response['link'];
        }
        
        $post->set('subject', $subject)
            ->set('body', $body)
            ->set('tag_ids', $tagIds)
            ->set('name', isset($profileData['name']) ? $profileData['name'] : null)
            ->set('is_active', (int)$isActive)
            ->set('image_url', $imageUrl)
            ->save();

        header("Location: " . $post->getUrl());
    }

    /**
     * @param $fileName
     * @return mixed
     * @throws \Imgur\InvalidArgumentException
     */
    private function uploadImage($fileName)
    {
        $client = new Client();
        $client->setOption('client_id', $this->_getConfigData('imgur_client_id'));
        $client->setOption('client_secret', $this->_getConfigData('imgur_client_secret'));

        $imageData = array(
            'image' => $fileName,
            'type' => 'file'
        );

        $basic = $client->api('image')->upload($imageData);
        $response = $basic->getData();

        return $response;
    }

}
