<?php
/**
 * @package plg_kraken for Joomla!
 * @version 1.0.3
 * @author Christoph Schafflinger
 * @copyright (C) 2017 Christoph Schafflinger
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
**/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class plgSystemKraken extends JPlugin
{
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);
	}

	function onContentBeforeSave($context,$article,$isNew)
	{
		// skip if not media manager
		if($context != "com_media.file")
		{
			return;
		}

		require_once(__DIR__."/lib/Kraken.php");

		if(!isset($kraken)){
			$kraken = new Kraken($this->params->get( 'apikey', '' ), $this->params->get( 'apisecret', '' ));
		}

		$kparams = array(
		    "file" => $article->tmp_name,
		    "wait" => true
		);

		if(!$this->params->get('krakendefaults', 1)){
			// "Use Kraken Defaults" set to no
			if($this->params->get('optimization', 1)){
				// Lossy Optimization Settings
				$kparams["lossy"] = true;
				$kparams["quality"] = (int)$this->params->get('qual', 70);
				$kparams["sampling_scheme"] = $this->params->get('chroma', '4:2:2');
			}else{
				// Lossless Optimization
				$kparams["lossy"] = false;
			}

			//Meta Settings
			if($this->params->get('metapreserve', 0)){
				// Preserve some or all Meta Data
				$meta = array();
				$meta[] = ($this->params->get('metaprofile', 0) ? "profile" : "");
				$meta[] = ($this->params->get('metadate', 0) ? "date" : "");
				$meta[] = ($this->params->get('metacopyright', 0) ? "copyright" : "");
				$meta[] = ($this->params->get('metageotag', 0) ? "geotag" : "");
				$meta[] = ($this->params->get('metaorientation', 0) ? "orientation" : "");
				// add cleaned up array
				$kparams["preserve_meta"] = array_values(array_filter($meta, function($value) { return $value !== ''; }));
			}
		}else{
			$kparams["lossy"] = true;
		}

		// Image Orientation
		$kparams["auto_orient"] = ($this->params->get('autoorientation', 0) ? true : false );

		$data = $kraken->upload($kparams);

		if ($data["success"]) {
		    if($this->grab_image($data["kraked_url"], $article->tmp_name) !== false){
		    	JFactory::getApplication()->enqueueMessage("Kraken successfully saved " . $this->formatSizeUnits($data["saved_bytes"]) . " on " . substr($article->filepath, strlen(COM_MEDIA_BASE)), "notice");
		    	// Update article Object with optimized Filesize
		    	$article->set('size',$data["kraked_size"]);
		    }else{
		    	JFactory::getApplication()->enqueueMessage("Fetching File from Kraken failed.", "error");
		    }

		} else {
		    JFactory::getApplication()->enqueueMessage("Kraken failed: " . $data["message"], "error");
		}

		return true;
	}

	private function grab_image($url,$saveto){
	    $ch = curl_init ($url);
	    curl_setopt($ch, CURLOPT_HEADER, 0);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
	    curl_setopt($ch, CURLOPT_CAINFO, __DIR__."/lib/dlkrakenio.crt");
	    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
	    $raw=curl_exec($ch);
	    curl_close ($ch);
	    return ($raw === false ? false : file_put_contents($saveto, $raw));
	}

	private function formatSizeUnits($bytes){
        if ($bytes >= 1073741824)
        {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        }
        elseif ($bytes >= 1048576)
        {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        }
        elseif ($bytes >= 1024)
        {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        }
        elseif ($bytes > 1)
        {
            $bytes = $bytes . ' bytes';
        }
        elseif ($bytes == 1)
        {
            $bytes = $bytes . ' byte';
        }
        else
        {
            $bytes = '0 bytes';
        }

        return $bytes;
	}
}
?>