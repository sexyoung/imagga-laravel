<?php
/**
 * source code generated by http://restunited.com using Swagger Codegen
 * for any feedback/issue, please send to feedback{at}restunited.com
 *
 * swagger-codegen: https://github.com/wordnik/swagger-codegen
 *
 * @package default
 */


/**
 * APIClient.php
 */
namespace Sexyoung\ImaggaLaravel;

/* Autoload the model definition files */


/**
 *
 * @param string  $className the class to attempt to load
 */
function swagger_autoloader($className) {
	$currentDir = dirname(__FILE__);
	if (file_exists($currentDir . '/' . $className . '.php')) {
		include $currentDir . '/' . $className . '.php';
	} elseif (file_exists($currentDir . '/models/' . $className . '.php')) {
		include $currentDir . '/models/' . $className . '.php';
	}
}


spl_autoload_register('Sexyoung\ImaggaLaravel\swagger_autoloader');


class APIClient {

	public static $POST = "POST";
	public static $GET = "GET";
	public static $PUT = "PUT";
	public static $DELETE = "DELETE";
	public static $PATCH = "PATCH";

	// authentication
	public $apiKey = '';
	public $apiKeyType = '';
	public $apiKeyName = '';
	public $apiKeyPrefix = '';
	public $username='';
	public $password='';


	/**
	 *
	 * @param string  $apiServer (optional) the address of the API server
	 */
	function __construct($apiServer='https://api.imagga.com/v1') {
		$this->apiServer = $apiServer;
	}


	/**
	 *
	 * @param string  $resourcePath path to method endpoint
	 * @param string  $method       method to call
	 * @param array   $queryParams  parameters to be place in query URL
	 * @param array   $postData     parameters to be placed in POST body
	 * @param array   $headerParams parameters to be place in request header
	 * @param unknown $require_auth
	 * @return mixed
	 */
	public function callAPI($resourcePath, $method, $queryParams, $postData,
		$headerParams, $require_auth) {

		$headers = array();

		// Allow API key from $headerParams to override default
		//$added_api_key = False;
		if ($headerParams != null) {
			foreach ($headerParams as $key => $val) {
				$headers[] = "$key: $val";
				/* comment out to support other api key name
				if ($key == 'api_key') {
				    $added_api_key = True;
				}*/
			}
		}

		// authentication setting
		if ($require_auth == FALSE) {
			// no authentication required
		} else if ($this->apiKeyType == 'header') {
			// only header supports prefix
			if ($this->apiKeyPrefix == '') {
				$headers[] = "{$this->apiKeyName}: {$this->apiKey}";
			} else {
				$headers[] = "{$this->apiKeyName}: {$this->apiKeyPrefix} {$this->apiKey}";
			}
		} else if ($this->apiKeyType == 'query') {
			$queryParams[$this->apiKeyName] = $this->apiKey;
		} else if ($this->apiKeyType == 'form') {
			$postData[$this->apiKeyName] = $this->apiKey;
		} else if ($this->username and $this->password) { // HTTP basic
			$http_auth_header = 'Basic '.base64_encode("{$this->username}:{$this->password}");
			$headers[] = "Authorization: {$http_auth_header}";
		}

		// Set empty expect
		$headers[] = "Expect:";

		// form data
		if ($postData and in_array('Content-Type: application/x-www-form-urlencoded', $headers)) {
			$postData = http_build_query($postData);
		}
		else if ((is_object($postData) or is_array($postData)) and !in_array('Content-Type: multipart/form-data', $headers)) { // json model
			$postData = json_encode($this->sanitizeForSerialization($postData));
		}

		$url = $this->apiServer . $resourcePath;

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_TIMEOUT, 10);
		// return the result on success, rather than just TRUE
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

		if (! empty($queryParams)) {
			$url = ($url . '?' . http_build_query($queryParams));
		}

		if ($method == self::$POST) {
			curl_setopt($curl, CURLOPT_POST, true);
			$file = (explode(";",str_replace("@","",$postData['image']))[0]);
			// curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
			curl_setopt($curl, CURLOPT_POSTFIELDS, [
				'file' => new \CURLFile($file), 
			]);
		} else if ($method == self::$PUT) {
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
			curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
		} else if ($method == self::$DELETE) {
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
			curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
		} else if ($method == self::$PATCH) {
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PATCH");
			curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
		} else if ($method != self::$GET) {
			throw new \Exception('Method ' . $method . ' is not recognized.');
		}
		curl_setopt($curl, CURLOPT_URL, $url);

		// Set agent
		curl_setopt($curl, CURLOPT_USERAGENT, 'Swagger/PHP/0.1.0/beta');

		// Make the request
		$response = curl_exec($curl);
		$response_info = curl_getinfo($curl);

		// Handle the response
		if ($response_info['http_code'] == 0) {
			throw new \Exception("TIMEOUT: api call to " . $url .
				" took more than 5s to return" );
		} else if ($response_info['http_code'] >= 200 && $response_info['http_code'] <= 299) {
			$data = json_decode($response);
			if (json_last_error() > 0) {
				$data = $response;
			}
		} else if ($response_info['http_code'] == 401) {
			throw new \Exception("Unauthorized API request to " . $url .
				": ".serialize($response) );
		} else if ($response_info['http_code'] == 404) {
			$data = null;
		} else {
			throw new \Exception($response_info['http_code']." Error ($url): ".
				"response body => ". serialize($response));
		}

		return $data;
	}


	/**
	 * Build a JSON POST object
	 *
	 * @param unknown $data
	 * @return unknown
	 */
	protected function sanitizeForSerialization($data) {
		if (is_scalar($data) || null === $data) {
			$sanitized = $data;
		} else if ($data instanceof \DateTime) {
			$sanitized = $data->format(\DateTime::ISO8601);
		} else if (is_array($data)) {
			foreach ($data as $property => $value) {
				$data[$property] = $this->sanitizeForSerialization($value);
			}
			$sanitized = $data;
		} else if (is_object($data)) {
			$values = array();
			foreach (array_keys($data::$swaggerTypes) as $property) {
				if (!is_null($this->sanitizeForSerialization($data->$property))) {
					$values[$data::$attributeMap[$property]] = $this->sanitizeForSerialization($data->$property);
				}
			}
			$sanitized = $values;
		} else {
			$sanitized = (string)$data;
		}

		return $sanitized;
	}


	/**
	 * Take value and turn it into a string suitable for inclusion in
	 * the path, by url-encoding.
	 *
	 * @param string  $value a string which will be part of the path
	 * @return string the serialized object
	 */
	public static function toPathValue($value) {
		return rawurlencode($value);
	}


	/**
	 * Take value and turn it into a string suitable for inclusion in
	 * the query, by imploding comma-separated if it's an object.
	 * If it's a string, pass through unchanged. It will be url-encoded
	 * later.
	 *
	 * @param object  $object an object to be serialized to a string
	 * @return string the serialized object
	 */
	public static function toQueryValue($object) {
		if (is_array($object)) {
			return implode(',', $object);
		} else {
			return $object;
		}
	}


	/**
	 * Just pass through the header value for now. Placeholder in case we
	 * find out we need to do something with header values.
	 *
	 * @param string  $value a string which will be part of the header
	 * @return string the header string
	 */
	public static function toHeaderValue($value) {
		return $value;
	}


	/**
	 * Deserialize a JSON string into an object
	 *
	 * @param unknown $data
	 * @param string  $class class name is passed as a string
	 * @return object an instance of $class
	 */
	public static function deserialize($data, $class) {
		if (null === $data) {
			$deserialized = null;
		} else if (strcasecmp(substr($class, 0, 6), 'array[') == 0) {
			$subClass = substr($class, 6, -1);
			$values = array();
			foreach ($data as $value) {
				$values[] = self::deserialize($value, $subClass);
			}
			$deserialized = $values;
		} elseif ($class == 'DateTime') {
			$deserialized = new \DateTime($data);
		} elseif (in_array($class, array('string', 'int', 'integer', 'number', 'float', 'bool'))) {
			if ($class == 'number') { //map number to float
				$class = 'float';
			}
			settype($data, $class);
			$deserialized = $data;
		} else {
			$class = "Sexyoung\\ImaggaLaravel\\models\\".$class;
			$instance = new $class();
			foreach ($instance::$swaggerTypes as $property => $type) {
				$property_key = $instance::$attributeMap[$property];
				if (isset($data->$property)) {
					$instance->$property = self::deserialize($data->$property_key, $type);
				}
			}
			$deserialized = $instance;
		}

		return $deserialized;
	}


	/**
	 * Get the MIME type of a file
	 *
	 * @param string  file name with full path
	 * @param unknown $file
	 * @return string MIME type
	 */
	public static function getFileMimeType($file) {
		if (function_exists('finfo_file')) {
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$type = finfo_file($finfo, $file);
			finfo_close($finfo);
		} else {
			require_once 'upgradephp/ext/mime.php';
			$type = mime_content_type($file);
		}

		if (!$type || in_array($type, array('application/octet-stream', 'text/plain'))) {
			$secondOpinion = exec('file -b --mime-type ' . escapeshellarg($file), $foo, $returnCode);
			if ($returnCode === 0 && $secondOpinion) {
				$type = $secondOpinion;
			}
		}

		if (!$type || in_array($type, array('application/octet-stream', 'text/plain'))) {
			require_once 'upgradephp/ext/mime.php';
			$exifImageType = exif_imagetype($file);
			if ($exifImageType !== false) {
				$type = image_type_to_mime_type($exifImageType);
			}
		}

		return $type;
	}


}
