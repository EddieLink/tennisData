<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use GuzzleHttp\Client;
use PHPHtmlParser\Dom;

use Illuminate\Support\Facades\Cache;

class InitialController extends Controller
{
    public function index($maximum_age, Request $request)
    {
            $col = collect(Cache::get('players'));
            return $col;
            $col2 = $col->filter(function($value, $key) use($maximum_age){
                return ($value['plays'] == " Right-Handed, One-Handed Backhand " || $value['plays'] == " Left-Handed, One-Handed Backhand ") 
                    && filter_var($value['age'], FILTER_SANITIZE_NUMBER_INT)<=$maximum_age;
            });
            $col3 = $col->filter(function($value, $key) use($maximum_age){
                return ($value['plays'] == " Right-Handed, Two-Handed Backhand " || $value['plays'] == " Left-Handed, Two-Handed Backhand ") 
                    && filter_var($value['age'], FILTER_SANITIZE_NUMBER_INT)<=$maximum_age;
            });

            return ['Difference' => $col3->count() - $col2->count(), 'Total' => $col3->count() + $col2->count()];
            // $col = $co2l->sortBy(function($value, $key){
            //     return filter_var($value['ranking'], FILTER_SANITIZE_NUMBER_INT);
            // });
            return $col;
	dd(Cache::get('players'));

	// $players =$res->
    }
        private static $baseUrl = 'http://www.atpworldtour.com';


    public static function updateCache()
   {

        if(!Cache::has('atpwebsite'))
        {
            self::updateAtpWebsiteCache();
        }
        $res = Cache::get('atpwebsite');
        $res = $res->find('tr');

        $players = [];
        $it = 0;
        foreach($res as $k=>$v)
        {
                try{
                 $player = $v->find('.player-cell');
                $link = $player->find('a');
                $players[$it]['name'] = $link->innerHtml;
                $players[$it]['age'] = $v->find('.age-cell')->innerHtml;
                $players[$it]['ranking'] = $it+1;
                $players[$it]['link'] = $link->getAttribute('href');
             }
             catch(\Exception $ex)
             {
                continue;
             }
             $it++;
            
        }
        Cache::forget('players');
        Cache::forever('players', $players);
    }
    public static function updateAtpWebsiteCache()
    {
            $dom = new Dom;
            $res = $dom->loadFromUrl(self::$baseUrl.'/en/rankings/singles?rankDate=2016-10-17&rankRange=1-1000')->find('.mega-table');
            Cache::forever('atpwebsite', $res);
    }
    public static function updateIndividualPlayerCache()
    {
            $players = Cache::get('players');
            $dom = new Dom;
            foreach($players as $k=>$p)
            {

                $link = $p['link'];
                $res2 = $dom->loadFromUrl(self::$baseUrl.$link);
                $players[$k]['plays'] = $res2->find('tr',1)->find('td',2)->find('.table-value')->innerHtml;
                // if($k>20)
                    // break;
            }
            Cache::forget('players');
            Cache::forever('players', $players);
    }
}
