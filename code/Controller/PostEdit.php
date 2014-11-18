<?php


use Guzzle\Http\Client;

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
        //$imageUrl = isset($_POST['image_url']) ? $_POST['image_url'] : null;
        $imageUrl = $_FILES['image_url'];
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

        $fileName = $_FILES['image_url']['tmp_name'];
        $handle = fopen($fileName, 'r');
        $image = fread($handle, filesize($fileName));
        $imagePost   = array('image' => base64_encode($image));

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'https://api.imgur.com/3/image.json');
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: Client-ID ' . 'becc794036ea803'));
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $imagePost);
        $out = curl_exec($curl);
        curl_close ($curl);

        $returnData = json_decode($out,true);
        $imageUrl = $returnData['data']['link'];

        $post->set('subject', $subject)
            ->set('body', $body)
            ->set('tag_ids', $tagIds)
            ->set('name', isset($profileData['name']) ? $profileData['name'] : null)
            ->set('is_active', (int)$isActive)
            ->set('image_url', $imageUrl)
            ->save();

        header("Location: " . $post->getUrl());
    }
}
