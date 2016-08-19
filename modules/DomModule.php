<?php

/**
 * Created by PhpStorm.
 * User: vince
 * Date: 8/16/2016
 * Time: 9:02 PM
 */
class DomModule
{
    /**
     * @var object
     */
    protected $domService;

    /**
     * @var array
     */
    protected $domSeenLinks = array();

    /**
     * @var array
     */
    protected $domNotSeenLinks = array();


    protected $allScanedLinks = array();


    public function __construct()
    {
        $this->domService = new \DOMDocument();
    }

    /**
     * @var array
     */
    protected $websiteInfo;

    /**
     * @return array
     */
    public function getWebsiteInfo()
    {
        return $this->websiteInfo;
    }

    /**
     * @param array $websiteInfo
     */
    public function setWebsiteInfo($websiteInfo)
    {
        $this->websiteInfo = $websiteInfo;
    }

    /**
     * @param string $webPage
     */
    public function scanDom($webPage)
    {
        $this->domService->loadHTML($webPage);
        $this->scanLinks();
    }

    protected function scanLinks()
    {
        foreach ($this->domService->getElementsByTagName('a') as $link) {
            $this->setDomNotSeenLinks($link->getAttribute('href'));
        }
    }

    public function removeFromDomNotSeenLinks($link)
    {
        if(($key = array_search($link, $this->domNotSeenLinks)) !== false) {
            unset($this->domNotSeenLinks[$key]);
        }
    }

    /**
     * @return array
     */
    public function getDomSeenLinks()
    {
        return $this->domSeenLinks;
    }

    /**
     * @param string $link
     */
    public function setDomSeenLinks($link)
    {
        array_push($this->domSeenLinks, $link);
    }

    /**
     * @return array
     */
    public function getDomNotSeenLinks()
    {
        return $this->domNotSeenLinks;
    }

    /**
     * @param string $link
     */
    public function setDomNotSeenLinks($link)
    {
        if (!in_array($link, $this->domSeenLinks))
            array_push($this->domNotSeenLinks, $link);
    }

    /**
     * @return array
     */
    public function getAllScanedLinks()
    {
        return $this->allScanedLinks;
    }

    /**
     * @param string $link
     */
    public function setAllScanedLinks($link)
    {
        array_push($this->allScanedLinks, $link);
    }

}