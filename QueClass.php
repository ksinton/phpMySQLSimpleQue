<?php
class Que {

    protected $maxConcurrentJobs = 1;

    public $dbConnection;  //the datbase connection object

    public function __construct($dbConnection) {

        if(!$dbConnection) {
            throw new Exception('No DB Connection Object Provided');
            die();
        }

        $this->dbConnection = $dbConnection;

    }

    public function addQueJob($address) {

        $cleanAddress = $this->dbConnection->escape_string($address);

        $sql = "INSERT INTO que SET url = '". $cleanAddress  . "' , status = 0";

        if ($this->dbConnection->query($sql) === TRUE) {
            return $this->dbConnection->insert_id;
        } else {
            echo "Error: " . $sql . "<br>" . $this->dbConnection->error;
            return;
        }

        return $jobId;

    }


	protected function getUrlHtml($url) {
	
        //using PHPs build in curl library, this will need to be enabled 
        $curlResource = curl_init();

        curl_setopt($curlResource, CURLOPT_URL, $url);

        //set curl return this transfer as a string
        curl_setopt($curlResource, CURLOPT_RETURNTRANSFER, 1);

        $html = curl_exec($curlResource);
        
        curl_close($curlResource);  
		
		return $html;
	}
	
	protected function getNextJob() {
	
		$sql = "SELECT * FROM que WHERE status = 0 ORDER BY `jobId` ASC LIMIT 1";

        if(!$result = $this->dbConnection->query($sql)){
            die('There was an error running the query [' . $this->dbConnection->error . ']');
        }

        $job = $result->fetch_assoc();
        
        return $job;
		
	}
	
	protected function jobsInProgress() {
	
		$sql = "SELECT count(*) as jobsRunning FROM que WHERE status = 1";

        if(!$result = $this->dbConnection->query($sql)){
            die('There was an error running the query [' . $this->dbConnection->error . ']');
        }

        $job = $result->fetch_assoc();
        
        return $job['jobsRunning'];
		
	}
	
	protected function setJobStatus($jobId, $statusCode) {
		
		$sql = "UPDATE que SET status = ". $statusCode  . " WHERE jobId = " . $jobId;

        if (!$this->dbConnection->query($sql) === TRUE) {
            echo "Unable to set status for job. Databse Error  : " . $sql . "<br>" . $this->dbConnection->error;
            return false;
        }
        
        return true;
        
	}
	
	protected function storeHtml($jobId, $html) {
		
		$cleanHtml = $this->dbConnection->escape_string($html);
		
		$sql = "INSERT INTO queJobsHtml SET jobId = " . $jobId . ", html = '". $cleanHtml  . "'";

        if (!$this->dbConnection->query($sql) === TRUE) {
            error_log("Unable to store HTML for job. Databse Error  : " . $sql . "<br>" . $this->dbConnection->error);
            return false; 
        }
        
        return true; 
	}

    public function runQue() {
    
    	//get the num jobs in progress
    	$numJobsInProgress = $this->jobsInProgress();
    	
    	//if there are already too many jobs running than stop and wait for the next cycle
    	if (($numJobsInProgress + 1) > $this->maxConcurrentJobs) {
    		return; 
    	}
    
    	//get the next job
    	$job = $this->getNextJob();

		//set the job status to in progress
		$this->setJobStatus($job['jobId'], 1);
        
        $urlToFetch = $job['url'];
        
        // grab the url with curl
        $html = $this->getUrlHtml($urlToFetch);
        
        if (!html) {
        	echo "no html";
            error_log("unable to fetch HTML for job: " . $job['jobId']);
            //set the job status to failed
			$this->setJobStatus($job['jobId'], 3);
			die();
        }   
        
        
        //store the HTML in the Databsse
        $this->storeHtml($job['jobId'], $html);

		//set the job status to complete
		$this->setJobStatus($job['jobId'], 2);
    }


    public function checkJob($jobId) {

        $cleanJobId = $this->dbConnection->escape_string($jobId);

        if(!is_numeric($cleanJobId)) {
            return "invalid input, non integer";
        }

        $sql = "SELECT * FROM que WHERE jobId = ". $cleanJobId;

        if(!$result = $this->dbConnection->query($sql)){
            die('There was an error running the query [' . $this->dbConnection->error . ']');
        }

        $job = $result->fetch_assoc();
        
        if($job['status'] == 0) {
        	
        	return "{'status':'not proccessed','statusCode':'". $job['status'] ."'}";
        	
        } else if ($job['status'] == 1) {
        	
        	return "{'status':'in proccess','statusCode':'". $job['status'] ."'}";
        	
        } else if ($job['status'] == 2) {
        
        	return "{'status':'completed','statusCode':'". $job['status'] ."'}";
        	
        }  else if ($job['status'] == 3) {
        
        	return "{'status':'failed','statusCode':'". $job['status'] ."'}";
        	
        }

    }

}