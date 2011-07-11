<?php
/**
*	A very quick and rough PHP class to scrape data from google+
*	Copyright (C) 2011  Mabujo
*	http://plusdevs.com
*	http://plusdevs.com/googlecard-googleplus-php-scraper/
*
*	This program is free software: you can redistribute it and/or modify
*	it under the terms of the GNU General Public License as published by
*	the Free Software Foundation, either version 3 of the License, or
*	(at your option) any later version.
*
*	This program is distributed in the hope that it will be useful,
*	but WITHOUT ANY WARRANTY; without even the implied warranty of
*	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*	GNU General Public License for more details.
*
*	You should have received a copy of the GNU General Public License
*	along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

class googleCard
{
	// The base g+ URL
	public $gplus_url = 'http://plus.google.com/';

	// set a plausible user agent
	public $user_agent = 'Mozilla/5.0 (X11; Linux x86_64; rv:5.0) Gecko/20100101 Firefox/5.0';

	/*
	* whether to cache the data or not
	* no cache = 0
	* cache = 1
	*/
	public $cache_data = '0';

	// how many hours to cache for
	public $cache_hours = '4';

	// cache file name
	public $cache_file = '';

	// constructor
	function __construct($id = '')
	{
		if (!empty($id) && is_numeric($id))
		{
			// build our google+ url
			$this->url = $this->gplus_url . $id;
			$this->user_id = $id;
		}
	}

	// main handler function, call it from your script
	public function googleCard()
	{
		// if we're using caching
		if ($this->cache_data > 0)
		{
			$html = $this->ghettoCache();
			return $html;
		}
		// don't cache, use a transient instead
		else
		{
			// see if our data is in the db
			if (false === ($data = get_transient('googlecards')))
			{
				// if data is not in the db, fetch the data and store
				$data = $this->handleLoad();
				set_transient('googlecards', $data, 60*60*$this->cache_hours);
			}

			// return the data for both cases
			return $data;
		}
	}

	// handles loading the page via curl or get_file_contents
	protected function handleLoad()
	{

		/*
		*	if safe mode or open_basedir is set, skip to using file_get_contents
		*	(fixes "CURLOPT_FOLLOWLOCATION cannot be activated" curl_setopt error)
		*/
		if(ini_get('safe_mode') || ini_get('open_basedir'))
		{
			// do nothing (will pass on to getPafeFile/get_file_contents as isset($curlHtml) will fail)
		}
		else
		{
			// load the page
			$this->getPageCurl();

			// parse the returned html for the data we want
			$curlHtml = $this->parseHtml();
		}	

		// see if curl managed to get data
		// if not, try with get_file_contents
		if (isset($curlHtml) && !empty($curlHtml['name']) && !empty($curlHtml['count']) && !empty($curlHtml['img']))
		{
			return $curlHtml;
		}
		else
		{
			// try loading with file_get_contents instead
			$this->getPageFile();

			// parse
			$data = $this->parseHtml();

			// return
			return $data;
		}
		
	}

	// parses through the returned html
	protected function parseHtml()
	{
		// parse the html to look for the h4 'have X in circles' element
		preg_match('/<h4 class="a-c-ka-Sf">(.*?)<\/h4>/s', $this->html, $matches);

		if (isset($matches) && !empty($matches)) 
		{
			$count = $matches[1];
			$circles = preg_replace('/[^0-9_]/', '', $count);
		}
		if (empty($circles))
		{
			$circles = 0;
		}		

		// parse the html for the user's name
		preg_match('/<span class="fn">(.*?)<\/span>/s', $this->html, $matches);
		$name = $matches[1];

		// parse the html for the img div
		preg_match('/<div class="a-Ba-V-z-N">(.*?)<\/div>/s', $this->html, $matches);
		$img_div = $matches[1];

		// parse the img div for the image src
		preg_match('/< *img[^>]*src *= *["\']?([^"\']*)/i', $img_div, $matches);
		$img = 'http:' . $matches[1];

		// put the data in an array
		$return = array('id' => $this->user_id, 'count' => $circles, 'name' => $name, 'img' => $img, 'url' => $this->url);

		return $return;
	}

	// use curl to load the page
	protected function getPageCurl()
	{
		// initiate curl with our url
		$this->curl = curl_init($this->url);

		// set curl options
		curl_setopt($this->curl, CURLOPT_HEADER, 0);
		curl_setopt($this->curl, CURLOPT_USERAGENT, $this->user_agent);
		curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, false);

		// execute the call to google+
		$this->html = curl_exec($this->curl);

		curl_close($this->curl);
	}

	// use file_get_contents to load the page
	protected function getPageFile()
	{
		// empty the html property (although it's probably empty anyway if we're here)
		$this->html = '';

		// get the data
		$this->html = file_get_contents($this->url);
	}

	// cache handling
	protected function ghettoCache()
	{
		// our cache file
		$file = $this->cache_file;
		$cache_time = ($this->cache_hours * 60) * 60;

		// if we have a cache file and it's within our expiry time
		if (file_exists($file) && (time() - $cache_time < filemtime($file)))
		{
			//open cached file
			$handle = fopen($file, "r");

			//read it
			$data = fgets($handle);

			//close it
			fclose($handle);

			$cached = get_object_vars(json_decode($data));

			if (is_null($cached['name']) || is_null($cached['url']) || $this->user_id != $cached['id']) 
			{
				$html = $this->doCache($file);
				return $html;
			}
			else
			{
				// json decode, put into array and return
				return get_object_vars(json_decode($data));
			}
		}
		// we don't have a cache file
		// call google+ and cache
		else
		{
			$html = $this->doCache($file);
			return $html;
		}
	}

	// the caching function
	protected function doCache($file)
	{
		// get and parse the data
		$html = $this->handleLoad();

		// json encode the data
		$json = json_encode($html);

		// open the file
		$handle = fopen($file, 'w');

		// write data to file
		fwrite($handle, $json);

		// close file
		fclose($handle);

		// return data
		return $html;
	}
}
?>