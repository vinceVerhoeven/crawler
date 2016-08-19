<?php

/**
 * Created by PhpStorm.
 * User: vince
 * Date: 8/14/2016
 * Time: 8:41 PM
 */

include 'modules/DomModule.php';

class CurlHandler
{
    const DEBUG_ON = false;

    const MAX_CURL_REQUEST = 2;
    /**
     * @var bool
     */
    protected $scanFinished = false;

    /**
     * @var object
     */
    protected $domModule;

    /**
     * @var int
     */
    protected $curlRequestNumber = 0;

    /**
     * @var array
     */
    protected $curlResponseInfo;

    /**
     * @var string
     */
    protected $curlResponsePage;

    /**
     * @var string
     */
    protected $domain;

    public function __construct()
    {
        $this->domModule = new DomModule();
    }

    /**
     * @param string $url
     * @return string
     */
    public function curlRequest($url)
    {
        if ($this->isScanFinished() || empty($url)) {
            return 'scan is finished';
        }
        if ($this->getCurlRequestNumber() >= self::MAX_CURL_REQUEST) {
            return 'max requests made';
            exit;
        }

        $cleanUrl = $this->validateUrl($url);

        $curl = curl_init($cleanUrl);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);

        $this->setCurlResponsePage(curl_exec($curl));
        $this->setCurlRequestNumber(1);

        $this->setCurlResponseInfo(curl_getinfo($curl));
        $this->initializeCurlResponse();
    }

    protected function initializeCurlResponse()
    {
        $curlResponseInfo = $this->getCurlResponseInfo();

        switch ($curlResponseInfo['http_code']) {
            case "200":
                $this->curlResponse200($curlResponseInfo);
                echo "200 ";
                break;
            case "201":
                echo "201 Created";
                break;
            case "301":
                $this->curlResponse301($curlResponseInfo);
                echo "301 ";
                break;
            case "404":
                echo "404 page not found";
                break;
            default:
                echo "errorcode was " . $curlResponseInfo['http_code'];
        }
    }

    /**
     * @param array ($curlResponseInfo
     */
    protected function curlResponse200($curlResponseInfo)
    {
        // add to seen;

        // remove from notseen


        // todo check for external domain
        $url = $curlResponseInfo['url'];
        if (empty($this->getDomain())) {
            $this->setDomain(substr($url, 0, strrpos($url, '/')));
        }


        $urlPage = substr($url, strrpos($url, '/') + 1);

        $this->domModule->setDomSeenLinks($urlPage);
        
        $seemLinks = $this->domModule->getDomSeenLinks();
        $this->domModule->removeFromDomNotSeenLinks($urlPage);
        var_dump($seemLinks);

        $curlResponsePage = $this->getCurlResponsePage();
        $this->domModule->scanDom($curlResponsePage);

        $notSeen = $this->domModule->getDomNotSeenLinks();
        var_dump($notSeen);






        if (empty($this->domModule->getDomNotSeenLinks())) {
            $this->setScanFinished(true);
            $this->curlRequest('');
        } else {
            $newPage = current($this->domModule->getDomNotSeenLinks());
            $newUrl = $this->getDomain() . '/' . $newPage;
            $this->curlRequest($newUrl);
        }
    }


    /**
     * @param array $curlResponseInfo
     */
    protected function curlResponse301($curlResponseInfo)
    {
        // todo check redirect_count
        $this->curlRequest($curlResponseInfo['redirect_url']);
    }

    /**
     * @param array $curlResponseInfo
     */
    protected function curlResponse400($curlResponseInfo)
    {

    }

    /**
     * @param array $curlResponseInfo
     */
    protected function scanUrlLink($curlResponseInfo)
    {
        $url = $curlResponseInfo['url'];
        $urlPage = substr($url, strrpos($url, '/') + 1);
        var_dump($urlPage);
        if (empty($this->getDomain())) {
            $this->setDomain(substr($url, 0, strrpos($url, '/')));
        }
        if (empty($urlPage)) {
            $this->domModule->addToDomSeenLinks('index.html');
            $this->domModule->addToDomSeenLinks('index.php');
        } else {
            $this->domModule->addToDomSeenLinks($urlPage);
            $this->domModule->removeFromDomNotSeenLinks($urlPage);
        }
    }

    public function debug()
    {
        if (self::DEBUG_ON) {
            echo "<pre>";
            var_dump($this->domModule->getDomSeenLinks());
            var_dump($this->domModule->getDomNotSeenLinks());
            var_dump($this->getCurlResponseInfo());
            var_dump($this->getCurlRequestNumber());
        }
    }

    /**
     * @return boolean
     */
    public function isScanFinished()
    {
        return $this->scanFinished;
    }

    /**
     * @param boolean $scanFinished
     */
    public function setScanFinished($scanFinished)
    {
        $this->scanFinished = $scanFinished;
    }

    /**
     * @param $url
     * @return string
     */
    protected function validateUrl($url)
    {
        $cleanUrl = filter_var($url, FILTER_SANITIZE_URL);
        return $cleanUrl;
    }

    /**
     * @param mixed $msg
     */
    protected function stopCurl($msg)
    {
        $debug = json_encode($msg);
        echo json_encode(array('status' => 'error', 'debug' => $debug));
        exit;
    }

    /**
     * @return array
     */
    public function getCurlResponseInfo()
    {
        return $this->curlResponseInfo;
    }

    /**
     * @param array $curlResponseInfo
     */
    public function setCurlResponseInfo($curlResponseInfo)
    {
        $this->curlResponseInfo = $curlResponseInfo;
    }

    /**
     * @return string
     */
    public function getCurlResponsePage()
    {
        return $this->curlResponsePage;
    }

    /**
     * @param string $curlResponsePage
     */
    public function setCurlResponsePage($curlResponsePage)
    {
        $this->curlResponsePage = $curlResponsePage;
    }

    /**
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @param string $domain
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
    }

    /**
     * @return int
     */
    public function getCurlRequestNumber()
    {
        return $this->curlRequestNumber;
    }

    /**
     * @param int $curlRequestNumber
     */
    public function setCurlRequestNumber($curlRequestNumber)
    {
        $this->curlRequestNumber += $curlRequestNumber;
    }

}