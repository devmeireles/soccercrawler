<?php

/**
 * @@@@TODO
 * try to requests
 * data for GK
 */


error_reporting(E_ALL);
ini_set('display_errors', 1);   

require 'vendor/autoload.php';
require 'helper/functions.php';

use Guzzle\Http\Client as GuzzleHttpClient; 
use Guzzle\Http\Exception\ClientErrorResponseException;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\CssSelector;

class getPlayer{

    public function getPlayerData($id){
        $url = 'https://www.transfermarkt.com';
        $uri = '/player/profil/spieler/'.$id;

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

        $filter = '.pager';

        $checker = $crawler
            ->filter($filter)
            ->each(function (Crawler $node) {
                return $node->html();
            });

        if(count($checker) < 1){
            $filter = 'div.spielerdaten table.auflistung tr th';

            $dataLabel = $crawler
                ->filter($filter)
                ->each(function (Crawler $node) {
                    return toKey($node->text());
                });

            $filter = 'div.spielerdaten table.auflistung tr td';

            $dataValue = $crawler
                ->filter($filter)
                ->each(function (Crawler $node) {
                    return trim($node->text());
                });

            $playerInfo = [];
            foreach ($dataValue as $keyValue => $valueValue) {
                foreach ($dataLabel as $keyItem => $valueItem) {
                    if($keyItem === $keyValue){
                        $playerInfo[toKey($valueItem)] = [
                            $valueValue
                        ];
                    }
                }
            }


            $filter = 'h1[itemprop=name]';
            $playerName = $crawler
            ->filter($filter)
            ->each(function (Crawler $node) {
                return $node->text();
            });

            try{
                $filter = 'div.dataBild img';
                $playerPhoto = $crawler->filter($filter)->attr('src');
            }catch(\Exception $e){
                $playerPhoto = NULL;
            }

            try{
                $filter = '.dataZusatzbox .dataZusatzDaten .hauptpunkt';
                $playerClub = $crawler->filter($filter)->text();
            }catch(\Exception $e){
                $playerClub = NULL;
            }


            if(!$playerClub)
                $playerClub = [];

            try {
                $filter = '.dataZusatzDaten .mediumpunkt a';
                $playerLeague = trim($crawler->filter($filter)->text());
                    
            } catch (\Exception $e) {
                $playerLeague = NULL;
            }
        
            $filter = '.dataZusatzImage a img';
            $clubImage = trim($crawler->filter($filter)->attr('src'));

            if(!$clubImage)
                $clubImage = [];

            return $playerData = [
                'playerName'    =>  $playerName,
                'playerPhoto'   =>  $playerPhoto,
                'actualClub'    =>  [
                    'club'      =>  $playerClub,
                    'league'    =>  $playerLeague,
                    'image'     =>  $clubImage,
                ],
                'playerData'    =>  $playerInfo,
            ];
        }
    }
    
    public function getPlayerStats($id){
        $url = 'https://www.transfermarkt.com';
        $uri = '/player/leistungsdaten/spieler/'.$id.'/saison/ges/plus/1';

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

        $filter = '.pager';

        $checker = $crawler
            ->filter($filter)
            ->each(function (Crawler $node) {
                return $node->html();
            });

        if(count($checker) < 1){

            $filter = 'div.dataDaten p .dataItem';

            $dataItem = $crawler
                ->filter($filter)
                ->each(function (Crawler $node) {
                    return $node->text();
                });
            // unset($crawler);

            $filter = 'div.dataDaten p .dataValue';

            $dataValue = $crawler
                ->filter($filter)
                ->each(function (Crawler $node) {
                    return $node->text();
                });

            $playerInfo = [];
            foreach ($dataValue as $keyValue => $valueValue) {
                foreach ($dataItem as $keyItem => $valueItem) {
                    if($keyItem === $keyValue){
                        $playerInfo[toKey($valueItem)] = [
                            trim($valueValue)
                        ];
                    }
                }
            }

            $filter = 'table.items tbody tr td';

            $clubStatistics = $crawler
                ->filter($filter)
                ->each(function (Crawler $node) {
                    return $node->html();
                });

            $clubStatistics = $crawler
            ->filter($filter)
            ->each(function (Crawler $node) {
                return $node->html();
            });

            $leagueStatisticsObj = [];
            if($playerInfo['position'][0] === 'Goalkeeper'){
                for ($i=0; $i < count($clubStatistics); $i++) { 
                    $leagueStatisticsObj[] = [
                        'league'            => urlText($clubStatistics[++$i]),
                        'appearances'       => urlText($clubStatistics[++$i]),
                        'goals'             => $clubStatistics[++$i],
                        'ownGoals'          => $clubStatistics[++$i],
                        'substitutedOn'     => $clubStatistics[++$i],
                        'substitutedOff'    => $clubStatistics[++$i],
                        'yellowCards'       => $clubStatistics[++$i],
                        'yellow/redCards'   => $clubStatistics[++$i],
                        'LeaguredCardse'    => $clubStatistics[++$i],
                        'concededGoals'     => $clubStatistics[++$i],
                        'cleanSheets'       => $clubStatistics[++$i],
                        'minutesPlayed'     => $clubStatistics[++$i],
                    ];
                }
            }else{
                for ($i=0; $i < count($clubStatistics); $i++) { 
                    $leagueStatisticsObj[] = [
                        'league'            => urlText($clubStatistics[++$i]),
                        'appearances'       => urlText($clubStatistics[++$i]),
                        'goals'             => $clubStatistics[++$i],
                        'assists'           => $clubStatistics[++$i],
                        'ownGoals'          => $clubStatistics[++$i],
                        'substitutedOn'     => $clubStatistics[++$i],
                        'substitutedOff'    => $clubStatistics[++$i],
                        'yellowCards'       => $clubStatistics[++$i],
                        'yellow/redCards'   => $clubStatistics[++$i],
                        'LeaguredCardse'    => $clubStatistics[++$i],
                        'penaltyGoals'      => $clubStatistics[++$i],
                        'minutesPerGoal'    => $clubStatistics[++$i],
                        'minutesPlayed'     => $clubStatistics[++$i],
                    ];
                }
            }


            return $playerData = [
                'leagueStatistics'    =>  $leagueStatisticsObj,
            ];
        }
    }

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

        $filter = '.pager';

        $checker = $crawler
            ->filter($filter)
            ->each(function (Crawler $node) {
                return $node->html();
            });

        if(count($checker) < 1){

            $filter = 'table.items tbody tr td';

            $nationalStatistics = $crawler
                ->filter($filter)
                ->each(function (Crawler $node) {
                    return $node->text();
                });

            if(count($nationalStatistics) < 1){
                return NULL;
            }

            $nationalStatisticsComp = [];
            if(@$playerInfo['position'][0] === 'Goalkeeper'){
                for ($i=0; $i < count($nationalStatistics); $i++) { 
                    $nationalStatisticsComp[] = [
                        'league'            => $nationalStatistics[++$i],
                        'appearances'       => $nationalStatistics[++$i],
                        'goals'             => $nationalStatistics[++$i],
                        'assists'           => $nationalStatistics[++$i],
                        'yellowCards'       => $nationalStatistics[++$i],
                        'yellow/redCards'   => $nationalStatistics[++$i],
                        'LeaguredCardse'    => $nationalStatistics[++$i],
                        'concededGoals'     => $nationalStatistics[++$i],
                        'cleanSheet'        => $nationalStatistics[++$i],
                        'minutesPlayed'     => $nationalStatistics[++$i],
                    ];
                }
            }else{
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
                    'nation'            => $nationalTeam[++$i],
                    'shirt'             => $nationalTeam[++$i],
                    'nulled1'           => $nationalTeam[++$i],
                    'nationalTeam'      => $nationalTeam[++$i],
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

    public function getNationalLeague($id){
        $url = 'https://www.transfermarkt.com';
        $uri = '/player/detaillierteleistungsdaten/spieler/'.$id;

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

        $filter = '.pager';

        $checker = $crawler
            ->filter($filter)
            ->each(function (Crawler $node) {
                return $node->html();
            });

        if(count($checker) < 1){

            $filter = 'div.responsive-table #yw1 table.items  tbody tr td';

            $nationalLeague = $crawler
                ->filter($filter)
                ->each(function (Crawler $node) {
                    return $node->html();
                });

            if(count($nationalLeague) < 1){
                return NULL;
            }


            $nationalLeagueComp = [];
            for ($i=0; $i < count($nationalLeague); $i++) {
                $nationalLeagueComp[] = [
                    'season'            => $nationalLeague[+$i],
                    'nulled0'           => urlText($nationalLeague[++$i]),
                    'competiton'        => urlText($nationalLeague[++$i]),
                    'club'              => imgSrc($nationalLeague[++$i]),
                    'appearances'       => urlText($nationalLeague[++$i]),
                    'goals'             => $nationalLeague[++$i],
                    'assists'           => $nationalLeague[++$i],
                    'yellowCards'       => $nationalLeague[++$i],
                    'yellow/redCards'   => $nationalLeague[++$i],
                    'redCards'          => $nationalLeague[++$i],
                    'minutesPlayed'     => $nationalLeague[++$i],
                ];
            }

            $filter = 'div.responsive-table #yw2 table.items  tbody tr td';

            $domesticCupData = $crawler
                ->filter($filter)
                ->each(function (Crawler $node) {
                    return $node->html();
                });


            $domesticCupDataComp = [];
            for ($i=0; $i < count($domesticCupData); $i++) {
                $domesticCupDataComp[] = [
                    'season'            => $domesticCupData[+$i],
                    'nulled0'           => urlText($domesticCupData[++$i]),
                    'competiton'        => urlText($domesticCupData[++$i]),
                    'club'              => imgSrc($domesticCupData[++$i]),
                    'appearances'       => urlText($domesticCupData[++$i]),
                    'goals'             => $domesticCupData[++$i],
                    'assists'           => $domesticCupData[++$i],
                    'yellowCards'       => $domesticCupData[++$i],
                    'yellow/redCards'   => $domesticCupData[++$i],
                    'redCards'          => $domesticCupData[++$i],
                    'minutesPlayed'     => $domesticCupData[++$i],
                ];
            }

            $filter = 'div.responsive-table #yw3 table.items  tbody tr td';

            $internationalCupData = $crawler
                ->filter($filter)
                ->each(function (Crawler $node) {
                    return $node->html();
                });


            $internationalCupDataComp = [];
            for ($i=0; $i < count($internationalCupData); $i++) {
                $internationalCupDataComp[] = [
                    'season'            => $internationalCupData[+$i],
                    'nulled0'           => urlText($internationalCupData[++$i]),
                    'competiton'        => urlText($internationalCupData[++$i]),
                    'club'              => imgSrc($internationalCupData[++$i]),
                    'appearances'       => urlText($internationalCupData[++$i]),
                    'goals'             => $internationalCupData[++$i],
                    'assists'           => $internationalCupData[++$i],
                    'yellowCards'       => $internationalCupData[++$i],
                    'yellow/redCards'   => $internationalCupData[++$i],
                    'redCards'          => $internationalCupData[++$i],
                    'minutesPlayed'     => $internationalCupData[++$i],
                ];
            }

            return $getNationalLeague = [
                'nationalLeague'    =>  $nationalLeagueComp,
                'domesticCup'       =>  $domesticCupDataComp,
                'internationalCup'  =>  $internationalCupDataComp
            ];
        }
    }

    public function mountData($id){
        $data = [
            'id'                =>  $id,
            'playerInfo'        =>  $this->getPlayerData($id),
            'leagues'           =>  $this->getPlayerStats($id),
            'national'          =>  $this->getNationalData($id),
            'club'              =>  $this->getNationalLeague($id)
        ];

        return $data;
    }
}