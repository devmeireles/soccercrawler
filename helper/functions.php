<?php

require 'vendor/autoload.php';

use Guzzle\Http\Client as GuzzleHttpClient; 
use Guzzle\Http\Exception\ClientErrorResponseException;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\CssSelector;

function toKey($string) {
    $string = str_replace('-', ' ', $string);
    $string = trim($string);
    $string = str_replace(' ', '', lcfirst(ucwords($string)));

    return preg_replace('/[^A-Za-z0-9\-]/', '', $string);
} 

function imgSrc($item){
    try{
        $crawler = new Crawler($item);
        $filter = 'a img';
        return $data = $crawler->filter($filter)->attr('alt');
    }catch(\Exception $e){
        return NULL;
    }
}

function urlText($item){
    try{
        $crawler = new Crawler($item);
        $filter = 'a';
        return $data = $crawler->filter($filter)->text();
    }catch(\Exception $e){
        return NULL;
    }
}