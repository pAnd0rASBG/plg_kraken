<?php

/**
 * @package     Itsp.Plugin
 * @subpackage  Media-Action.kraken
 *
 * @copyright   (C) 2017 Christoph Schafflinger
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Itsp\Plugin\MediaAction\Kraken\Extension;

use Joomla\CMS\Application\CMSApplication;
use Joomla\Component\Media\Administrator\Plugin\MediaActionPlugin;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Media Manager Kraken Action
 *
 * @since  2.0.0
 */
final class Kraken extends MediaActionPlugin
{
	/**
	 * Application object.
	 *
	 * @var    CMSApplication
	 * @since  4.0.0
	 * @deprecated 6.0 Is needed for template overrides, use getApplication instead
	 */
	protected $app;

	/**
	 * Kraken authentication object
	 *
	 * @var array
	 * @since version 2.0.0
	 */
	protected array $auth = array();

    /**
     * The save event.
     *
     * @param   string   $context  The context
     * @param   object   $item     The item
     * @param   boolean  $isNew    Is new item
     * @param   array    $data     The validated data
     *
     * @return  void
     *
     * @since   4.0.0
     */
	public function onContentBeforeSave($context, $item, $isNew, $data = []): void
    {
        if ($context != 'com_media.file') {
            return;
        }

		// skip if upload is not an image
	    if (!in_array($item->extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
		    return;
	    }

		if (!class_exists('CURLStringFile' )) {
			$this->app->enqueueMessage('Kraken failed, CURL is not supported on your System', 'error');
		}

		// set up authentication object from plugin params
	    $this->auth = array(
		    "auth" => array(
			    "api_key" => $this->params->get('apikey', 0),
			    "api_secret" => $this->params->get('apisecret', 0)
		    )
	    );

		// set up the kraken params
	    $kParams = array (
			"file" => $item->data,
		    "wait" => true
	    );

	    if(!$this->params->get('krakendefaults', 1)){
		    // "Use Kraken Defaults" set to no
		    if($this->params->get('optimization', 1)){
			    // Lossy Optimization Settings
			    $kParams["lossy"] = true;
			    $kParams["quality"] = (int)$this->params->get('qual', 70);
			    $kParams["sampling_scheme"] = $this->params->get('chroma', '4:2:2');
		    }else{
			    // Lossless Optimization
			    $kParams["lossy"] = false;
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
			    $kParams["preserve_meta"] = array_values(array_filter($meta, function($value) { return $value !== ''; }));
		    }
	    }else{
		    $kParams["lossy"] = true;
	    }

	    // Image Orientation
	    $kParams["auto_orient"] = (bool) $this->params->get('autoorientation', 0);

		// convert to WebP
	    $kParams["webp"] = (bool) $this->params->get('webp', 0);;

	    $type = IMAGETYPE_JPEG;

	    switch ($item->extension) {
		    case 'gif':
			    $type = IMAGETYPE_GIF;
			    break;
		    case 'png':
			    $type = IMAGETYPE_PNG;
			    break;
		    case 'webp':
			    $type = IMAGETYPE_WEBP;
	    }

	    $kParams["mime"] = image_type_to_mime_type($type);
		$kParams["name"] = $item->name;

	    $response = $this->upload($kParams);

	    if ($response->success !== false) {
		    $this->app->enqueueMessage('Kraken saved ' . $this->formatSizeUnits($response->saved_bytes), 'info');
			if ($kParams["webp"] === true) {
				$item->extension = 'webp';
				$item->name = preg_replace("/(.*).(png|gif|jp(e)?g)/i", "$1.webp", $item->name);
			}
			if ($this->params->get('lowercase', 1)) {
				$item->name = strtolower($item->name);
			}
			$item->data = $response->data;
	    } else {
		    $this->app->enqueueMessage('Kraken failed: ' . $response->error, 'error');
	    }
	}

	/**
	 * Uploads a file to the Kraken API.
	 *
	 * @param array $kParams An array of parameters for the file upload. It must contain the 'file', 'name', and 'mime' keys.
	 *
	 * @return object The response object from the Kraken API. It contains the 'success' key, which is a boolean indicating whether
	 *                the upload was successful, and the 'error' key, which is a string containing the error message if the upload failed.
	 *                If the upload was successful, it also contains the 'data' key, which contains the response from the Kraken API.
	 *
	 * @since 2.0.0
	 */
	private function upload(array $kParams = array()): object
	{
		if (empty($kParams['file'])) {
			$response = new \StdClass();
			$response->success = false;
			$response->error = "File parameter was not provided";
			return $response;
		}

		$file = new \CURLStringFile($kParams['file'], $kParams['name'], $kParams['mime']);
		unset($kParams['file']);
		unset($kParams['name']);
		unset($kParams['mime']);

		$data = array_merge(array(
			"file" => $file,
			"data" => json_encode(array_merge($this->auth, $kParams))
		));

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, 'https://api.kraken.io/v1/upload');

		// Force continue-100 from server
		curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/40.0.2214.85 Safari/537.36");
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		curl_setopt($curl, CURLOPT_FAILONERROR, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $this->params->get('verifypeer', 1));
		curl_setopt($curl, CURLOPT_TIMEOUT, 30);

		$response = json_decode(curl_exec($curl));

		if ($response === null) {
			$response = new \StdClass();
			$response->success = false;
			$response->error = "cURL Error: " . curl_error($curl);
		} else {
			curl_setopt($curl, CURLOPT_POST, 0);
			curl_setopt($curl, CURLOPT_URL, $response->kraked_url);
			$response->data = curl_exec($curl);
		}

		curl_close($curl);

		return $response;
	}

	/**
	 * Formats the given number of bytes into a human-readable string representation.
	 *
	 * @param int $bytes The number of bytes to format.
	 * @return string The formatted string representation of the number of bytes.
	 *
	 * @since 1.0.0
	 */
	private function formatSizeUnits($bytes): string
	{
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
