<?php
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// jimport( 'joomla.plugin.plugin');
// jimport( 'joomla.html.parameter' );

class plgSystemKraken extends JPlugin
{
	function plgSystemKraken(&$subject, $config)
	{
		parent::__construct($subject, $config);
		$this->_plugin = JPluginHelper::getPlugin( 'system', 'Kraken' );
	}

	function onContentBeforeSave($context,$article,$isNew)
	{
        //$app = JFactory::getApplication();
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
		    "wait" => true,
		    "lossy" => true
		);

		$data = $kraken->upload($kparams);

		$log = "TMP: " . $article->tmp_name . "\n FILE: " . $article->filepath ."\n\n";

		if ($data["success"]) {
		    $log .= "Success. Optimized image URL: " . $data["kraked_url"];
		    if($this->grab_image($data["kraked_url"], $article->tmp_name) !== false){
		    	$log .= "\n\nSuccessfully saved to: " . $article->tmp_name ."\n";
		    	$log .= "Saved Bytes " . $data["saved_bytes"];
		    	JFactory::getApplication()->enqueueMessage("Kraken successfully saved " . $this->formatSizeUnits($data["saved_bytes"]) . " on " . substr($article->filepath, strlen(COM_MEDIA_BASE)) . " @Quali ".$this->params->get( 'qual', '70' ), "notice");
		    }else{
		    	$log .= "\n\nSaving from Kraken failed.";
		    	JFactory::getApplication()->enqueueMessage("Fetching File from Kraken failed.", "error");
		    }

		} else {
		    $log .= "Fail. Error message: " . $data["message"];
		    JFactory::getApplication()->enqueueMessage("Kraken failed: " . $data["message"], "error");
		}


		file_put_contents(__DIR__."/test.txt", $log);

		return true;
	}

	private function grab_image($url,$saveto){
	    $ch = curl_init ($url);
	    curl_setopt($ch, CURLOPT_HEADER, 0);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		// curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		// curl_setopt($ch, CURLOPT_CAINFO, __DIR__."/lib/krakenio.crt");
	    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
	    $raw=curl_exec($ch);
	    curl_close ($ch);
	    return file_put_contents($saveto, $raw);
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