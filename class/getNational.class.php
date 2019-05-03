<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);   

require 'vendor/autoload.php';

use Guzzle\Http\Client as GuzzleHttpClient; 
use Guzzle\Http\Exception\ClientErrorResponseException;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\CssSelector;

class getNational{
    public function getNationalData($id){
        $url = 'https://www.transfermarkt.com';
        $uri = '/player/nationalmannschaft/spieler/'.$id;

        $userAgent = 'Mozilla/5.0 (Windows NT 10.0)'
                . ' AppleWebKit/537.36 (KHTML, like Gecko)'
                . ' Chrome/48.0.2564.97'
                . ' Safari/537.36';
        $headers = array('User-Agent' => $userAgent);


        $client = new GuzzleHttpClient($url);
        $request = $client->get($uri, $headers);

        try {
            $response = $request->send();
            $body = $response->getBody(true);
        } catch (ClientErrorResponseException $e) {
            $responseBody = $e->getResponse()->getBody(true);
            echo $responseBody;
        }

        $crawler = new Crawler($body);

        $filter = 'table.items tbody tr td';

        $nationalStatistics = $crawler
            ->filter($filter)
            ->each(function (Crawler $node) {
                return $node->text();
            });

        $nationalStatisticsComp = [];
        for ($i=0; $i < count($nationalStatistics); $i++) { 
            $nationalStatisticsComp[] = [
                'league'            => $nationalStatistics[++$i],
                'appearances'       => $nationalStatistics[++$i],
                'goals'             => $nationalStatistics[++$i],
                'assists'           => $nationalStatistics[++$i],
                'yellowCards'       => $nationalStatistics[++$i],
                'yellow/redCards'   => $nationalStatistics[++$i],
                'LeaguredCardse'    => $nationalStatistics[++$i],
                'minutesPlayed'     => $nationalStatistics[++$i],
            ];
        }

        $filter = 'div.large-8 div.box table tbody';

        $nationalTeam = $crawler
            ->filter($filter)->eq(0)->filter('tr td')
            ->each(function (Crawler $node) {
                return trim($node->text());
            });

        $nationalTeamComp = [];
        for ($i=0; $i < count($nationalTeam); $i++) { 
            $nationalTeamComp[] = [
                'nulled0'           => $nationalTeam[++$i],
                'shirt'             => $nationalTeam[++$i],
                'nulled1'           => $nationalTeam[++$i],
                'nationalTeam1'     => $nationalTeam[++$i],
                'debut'             => $nationalTeam[++$i],
                'appearances'       => $nationalTeam[++$i],
                'goals'             => $nationalTeam[++$i],
                'coach'             => $nationalTeam[++$i],
                'age'               => $nationalTeam[++$i],
            ];
        }

        $nationalStatisticsObj = [
            'detailed'  =>  $nationalStatisticsComp,
            'stats'     =>  $nationalTeamComp
        ];

        return $nationalStatisticsObj;
    }
}