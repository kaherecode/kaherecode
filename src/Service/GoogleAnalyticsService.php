<?php

namespace App\Service;

use Google\Client;
use Google_Service_Analytics;
use Google_Service_Analytics_GaData;

class GoogleAnalyticsService
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var Google_Service_Analytics
     */
    protected $analytics;

    protected $gaViewId;

    protected static $GA_FILE = __DIR__ . "/../../vendor/analytics_credentials.json";

    public function __construct(string $gaViewId)
    {
        $this->gaViewId = $gaViewId;
        $this->client = new Client();
        $this->client->setAuthConfig(self::$GA_FILE);
        $this->client->setScopes(Google_Service_Analytics::ANALYTICS_READONLY);
        $this->analytics = new Google_Service_Analytics($this->client);
    }

    /**
     * Get analytics data from a view profile
     *
     * @param string $metrics   A comma-separated list of Analytics metrics.
     * @param string $startDate Start date for fetching data YYYY-MM-DD or today
     * @param string $endDate   End date for fetching data YYYY-MM-DD ot today
     * @param array  $params    Optional parameters. See google docs
     *                          https://developers.google.com/analytics/devguides/reporting/core/v3/reference
     *
     * @return Google_Service_Analytics_GaData
     */
    public function getData(
        string $metrics,
        string $startDate,
        string $endDate = "today",
        $params = []
    ): Google_Service_Analytics_GaData {
        return $this->analytics->data_ga->get(
            "ga:{$this->gaViewId}",
            $startDate,
            $endDate,
            $metrics,
            $params
        );
    }

    /**
     * Get the number of page views for a path
     *
     * @param string $path      The path for which you want the page views without the domain
     * @param string $startDate Start date for fetching data YYYY-MM-DD or today
     * @param string $endDate   End date for fetching data YYYY-MM-DD ot today
     *
     * @return int            The number of page views for the specified path
     */
    public function getPathPageViews(
        string $path,
        string $startDate,
        string $endDate = "today"
    ): int {
        $data = $this->getData(
            "ga:pageviews",
            $startDate,
            $endDate,
            ["filters" => "ga:pagePath=={$path}"]
        );

        return $data->getTotalsForAllResults()["ga:pageviews"];
    }
}
