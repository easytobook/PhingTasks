<?php

require_once "phing/Task.php";
/**
 * Notifies ErrBit about new deployment
 * For API @see http://help.airbrake.io/kb/api-2/notifier-api-version-23
 * Example:
 *     <errbit 
 *         host = "${errbit.host}"
 *         apikey = "${errbit.apikey}"
 *         repository = "${svn.repo}" 
 *         revision = "${svn.rev}"
 *         username = "${user}"
 *      />
 *      
 * @author alex@easytobook.com
 */
class ErrBitTask extends Task {
	const URL_PATH = '/deploys.txt';
	const TIMEOUT_CONNECTION_DEFAULT = 30;
	const TIMEOUT_EXECUTION_DEFAULT = 30;
	const ENVIRONEMENT_DEFAULT = 'production';
		
	/**
	 * @var string (required). hostname ( e.g. http://errbit.example.com )
	 */
	private $host = null;
	/**
	 * @var string (required)
	 */
	private $apiKey = null;
	/**
	 * @var string Which environment was just deployed to. For example, staging or production.
	 */
	private $env = self::ENVIRONEMENT_DEFAULT;
	/**
	 * @var string What's your version control repo's address.
	 */
	private $repository = null;
	/**
	 * @var string What's the version control revision.
	 */
	private $revision = null;
	/**
	 * @var string Who deployed?
	 */
	private $username = null;
	
	/**
	 * @var string
	 */
	private $urlPath = self::URL_PATH;
	/** 
	 * @var int seconds (Default: 5)
	 */
	private $timeoutConnection = self::TIMEOUT_CONNECTION_DEFAULT;
	/**
	 * @var int seconds (Default: 30)
	 */
	private $timeoutExecution = self::TIMEOUT_EXECUTION_DEFAULT;
	
	/**
	 * @param number $timeoutConnection
	 */
	public function setTimeoutConnection($timeoutConnection) {
		$this->timeoutConnection = $timeoutConnection;
	}

	/**
	 * @param number $timeoutExecution
	 */
	public function setTimeoutExecution($timeoutExecution) {
		$this->timeoutExecution = $timeoutExecution;
	}
	/**
	 * @param string $host
	 */
	public function setHost($host) {
		$this->host = $host;
	}
	/**
	 * @param string $urlPath
	 */
	public function setUrlPath($urlPath) {
		$this->urlPath = $urlPath;
	}
	/**
	 * @param string $apiKey
	 */
	public function setApiKey($apiKey) {
		$this->apiKey = $apiKey;
	}
	/**
	 * @param string $env
	 */
	public function setEnv($env) {
		$this->env = $env;
	}
	/**
	 * @param string $repository
	 */
	public function setRepository($repository) {
		$this->repository = $repository;
	}
	/**
	 * @param string $revision
	 */
	public function setRevision($revision) {
		$this->revision = $revision;
	}
	/**
	 * @param string $username
	 */
	public function setUsername($username) {
		$this->username = $username;
	}

	/* 
	 * Request:
	 * 	curl --data "api_key=<APIKEY>&deploy[rails_env]=production&deploy[scm_repository]=trunk&deploy[scm_revision]=123&deploy[local_username]=alex" http://errbit.example.com/deploys.txt
	 * Response:
	 *  <?xml version="1.0" encoding="UTF-8"?>
	 *  <deploy>
	 *   <repository>trunk</repository>
	 *   <message nil="true"/>
	 *   <_id>50f680439f63256fa0009fec</_id>
	 *   <environment>production</environment>
	 *   <username>alex</username>
	 *   <created-at type="datetime">2013-01-16T12:26:11+02:00</created-at>
	 *   <revision>123</revision>
	 *   <updated-at type="datetime">2013-01-16T12:26:11+02:00</updated-at>
	 *  </deploy>
	 *
	 * (non-PHPdoc)
	 * @see Task::main()
	 */
	public function main() {
		
		$this->validateAttributes();

		$fields = $this->buildFields();


		$url = rtrim($this->host,"/").$this->urlPath;
		$query = http_build_query($fields);
		
		$this->log($url.'?'.$query);
		//open connection
		$ch = curl_init();

		//set the url, number of POST vars, POST data
		curl_setopt($ch, CURLOPT_URL, 			 $url);
		curl_setopt($ch, CURLOPT_POST, 			 TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS,	 $query);
		curl_setopt($ch, CURLOPT_HEADER,		 0); // add or remove header from OUTPUT
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);	
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeoutConnection);
		curl_setopt($ch, CURLOPT_TIMEOUT,		 $this->timeoutExecution);

		//execute post
		$result = curl_exec($ch);
		
		$error = null;
		$error_code = curl_errno($ch);
		if ($error_code == CURLE_OK) {
			$this->log($result);
		} else {
			$error = curl_error($ch);
		}
		
		//close connection
		curl_close($ch);
		
		if ($error) {
			throw new BuildException("Task exited with error:($error_code) $error");
		}
	}
	
    protected function validateAttributes() {
    
        if ($this->host === null) {
            throw new BuildException("ErrBitTask. Host is not specified");
        }

        if ($this->apiKey === null) {
        	throw new BuildException("ErrBitTask. apiKey is not specified");
        }
        
        if ($this->env === null) {
        	throw new BuildException("ErrBitTask. Env is not specified");
        }
	}
	
	/**
	 * 
	 */
	 private function buildFields() {
		$fields = array(
			'api_key' => $this->apiKey,
			'deploy[rails_env]' => $this->env
		);
		if ($this->repository) {
			$fields['deploy[scm_repository]'] = $this->repository;
		}
		if ($this->revision) { 
			$fields['deploy[scm_revision]'] = $this->revision;
		}
		if ($this->username) {
			$fields['deploy[local_username]'] = $this->username;
		}
		return $fields;
	}

}