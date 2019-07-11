<?php

class TwitterGateway 
{
	/**
     * @var string
     */
    private $oauthAccessToken;

    /**
     * @var string
     */
    private $oauthAccessTokenSecret;

    /**
     * @var string
     */
    private $consumerKey;

    /**
     * @var string
     */
    private $consumerSecret;

    /**
     * @var string
     */
    private $query;

    /**
     * @var mixed
     */
    protected $oauth;

    /**
     * @var string
     */
    public $url;

    /**
     * Create the API gateway object. 
     */
    public function __construct()
    {
        $config = json_decode(file_get_contents('config.json'), true);

        $this->oauthAccessToken = $config['oauth_access_token'];
        $this->oauthAccessTokenSecret = $config['oauth_access_token_secret'];
        $this->consumerKey = $config['consumer_key'];
        $this->consumerSecret = $config['consumer_secret'];
    }

    /**
     * Get filtered tweets
     *
     * @param string $search The search term to filter tweets
     *
     * @return array         An array of tweet data
     */
    public function search(string $search)
    {
        $url = "https://api.twitter.com/1.1/search/tweets.json";
        $query = '?q='.$search.'&count=5';

        $response = $this->apiRequest($url, $query);
        $statuses = json_decode($response)->statuses;

        $tweets = [];

        if (!empty($statuses)) {

            foreach ($statuses as $status) {
                $embedHtml = $this->getStatusEmbedHtml($status);
                $tweets[] = $embedHtml;
            }

        }

        return $tweets;
    }

    /**
     * Perform a Twitter API Request
     *
     * @param string $url   The Twiiter API url endpoint to use.
     * @param string $query GET request query string to use.
     *
     * @throws \Exception
     *
     * @return string json results of api request.
     */
    private function apiRequest(string $url, string $query)
    {

        $this->setQuery($query);
        $this->buildOauth($url);

        $header = [$this->buildAuthorizationHeader($this->oauth), 'Expect:'];

        $options = [
            CURLOPT_HTTPHEADER => $header,
            CURLOPT_HEADER => false,
            CURLOPT_URL => $this->url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
        ];

        if ($query !== '') {
            $options[CURLOPT_URL] .= $this->query;
        }

        $handle = curl_init();
        curl_setopt_array($handle, $options);
        $json = curl_exec($handle);

        if (($error = curl_error($handle) !== '')) {
            curl_close($handle);

            throw new \Exception($error);
        }

        curl_close($handle);

        return $json;
    }
    
    /**
     * Set query string 
     *
     * @param string $string key and value pairs as a string
     */
    private function setQuery(string $string)
    {

        $queryParts = preg_replace('/^\?/', '', explode('&', $string));
        $params = [];

        foreach ($queryParts as $field) {
            if ($field !== '') {
                list($key, $value) = explode('=', $field);
                $params[$key] = $value;
            }
        }

        $this->query = '?' . http_build_query($params, '', '&');
    }

    /**
     * Build the Oauth object using config set in construct 
     *
     * @param string $url The Twiiter API url endpoint to use. 
     *
     * @return array      oauth parameters
     */
    private function buildOauth(string $url)
    {
        $oauth = [
            'oauth_consumer_key' => $this->consumerKey,
            'oauth_nonce' => time(),
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_token' => $this->oauthAccessToken,
            'oauth_timestamp' => time(),
            'oauth_version' => '1.0'
        ];

        if (!is_null($this->query)) {
            $queryParts = str_replace('?', '', explode('&', $this->query));

            foreach ($queryParts as $g) {
                $split = explode('=', $g);

                if (isset($split[1])) {
                    $oauth[$split[0]] = urldecode($split[1]);
                }
            }
        }

        $base = $this->buildBaseString($url, $oauth);
        $compositeKey = rawurlencode($this->consumerSecret) . '&' . rawurlencode($this->oauthAccessTokenSecret);
        $oauthSignature = base64_encode(hash_hmac('sha1', $base, $compositeKey, true));

        $oauth['oauth_signature'] = $oauthSignature;

        $this->url = $url;
        $this->oauth = $oauth;

        return $oauth;
    }

    /**
     * Private method to get embed html for a tweet
     *
     * @param object $status The tweet data result from the API
     *
     * @return string        Html string for displaying the tweet
     */
    private function getStatusEmbedHtml($status) 
    {
        $url = "https://publish.twitter.com/oembed";
        $query = '?url=https://twitter.com/Interior/status/'.$status->id;

        $embed = $this->apiRequest($url, $query);

        return json_decode($embed)->html;
    }

    /**
     * Private method to generate authorization header used by cURL
     *
     * @param array   $oauth  Array of oauth data 
     *
     * @return string $header Used by cURL for the request
     */
    private function buildAuthorizationHeader(array $oauth)
    {
        $header = 'Authorization: OAuth ';
        $values = [];

        foreach($oauth as $key => $value) {
            if (in_array($key, [
                'oauth_consumer_key', 
                'oauth_nonce', 
                'oauth_signature',
                'oauth_signature_method', 
                'oauth_timestamp', 
                'oauth_token', 
                'oauth_version'
            ])) {

                $values[] = "$key=\"" . rawurlencode($value) . "\"";

            }
        }   

        $header .= implode(', ', $values);

        return $header;
    }

    /**
     * Private method to generate the base string used by cURL
     *
     * @param string $baseUrl
     * @param array  $params
     *
     * @return string built base string
     */
    private function buildBaseString(string $baseUrl, array $params)
    {
        $parts = [];
        ksort($params);

        foreach ($params as $key => $value) {
            $parts[] = rawurlencode($key) . '=' . rawurlencode($value);
        }

        return "GET&" . rawurlencode($baseUrl) . '&' . rawurlencode(implode('&', $parts));
    }
}
