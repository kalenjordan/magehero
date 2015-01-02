<?php

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Commands_SitemapCommand extends Command {

    protected $_container;

    protected function configure()
    {
        $this->setName("sitemap:generate")
            ->setDescription("Sitemap Generator");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $basic = new \Sitemap\Sitemap\SitemapEntry('http://magehero.com/');
        $basic->setLastMod(time());

        $collection = new \Sitemap\Collection;
        $collection->addSitemap($basic);

        // Add User Profiles
        $select = $this->_getContainer()->User()->selectAll();

        $userRows = $this->_getContainer()->LocalConfig()->database()->fetchAll($select);
        foreach ($userRows as $userRow) {
            $userModel = $this->_getContainer()->User()->setData($userRow);

            $userEntry = new \Sitemap\Sitemap\SitemapEntry($userModel->getUrl());
            $userEntry->setLastMod($userModel->getUpdatedAt());
            $userEntry->setChangeFreq(\Sitemap\Sitemap\SitemapEntry::CHANGEFREQ_DAILY);
            $collection->addSitemap($userEntry);
        }


        // Add Posts

        $select = $this->_getContainer()->Post()->selectAll()
            ->where('posts.is_active = 1');

        $postRows = $this->_getContainer()->LocalConfig()->database()->fetchAll($select);

        foreach ($postRows as $postRow) {
            $postModel = $this->_getContainer()->Post()->setData($postRow);

            $postEntry = new \Sitemap\Sitemap\SitemapEntry($postModel->getUrl());
            $postEntry->setLastMod($postModel->getUpdatedAt());
            $postEntry->setChangeFreq(\Sitemap\Sitemap\SitemapEntry::CHANGEFREQ_WEEKLY);
            $collection->addSitemap($postEntry);
        }



        // There's some different formatters available.
        $collection->setFormatter(new \Sitemap\Formatter\XML\URLSet);

        file_put_contents('sitemap.xml', $collection->output());
    }


    protected function _getContainer()
    {
        if (isset($this->_container)) {
            return $this->_container;
        }

        $container = new Model_Container();

        $this->_container = $container;
        return $this->_container;
    }
}