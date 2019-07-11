<?php

require_once('Gateways/TwitterGateway.php');


class TwitterWidgetController {

	/**
     * @var string
     */
	private $defaultSearch = 'cats';

    /**
     * Public method to get embed html for tweets
     *
     * @param string $search The search term to filter tweets
     *
     * @return string Html string for displaying resulting tweets
     */
	public function getEmbedHtml(string $search) 
	{
		($search === '') ? $tweetSearch = $this->defaultSearch : $tweetSearch = $search;

		$gateway = new TwitterGateway();
		$statuses = $gateway->search($tweetSearch);

		return $statuses;
	}
}
