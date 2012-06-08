<?php
/**
 * @file
 * Provide access to the DoneDone IssueTracker API.  
 *
 * @see http://www.getdonedone.com/api
 *
 * @author Daniel Chen <daniel.chen@wearemammoth.com>
 *	   Mustafa shabib <mustafa.shabib@wearemammoth.com>
 */
class IssueTracker {
    protected $baseURL;
    protected $username;
    protected $token;

    /**
     * Default constructor
     *
     * @param string $domain - company's DoneDone domain
     * @param string $token - the project API token - optional if password is specified, will be used before password if both are specified
     * @param string $username - DoneDone username
     * @param string $password - DoneDone password - optional if API token is specified
     */
    function __construct($domain, $token, $username, $password) {
        $this->baseURL = "https://{$domain}.mydonedone.com/IssueTracker/API/";
        if(empty($token)){
		$this->token = $password;
		}else{
		$this->token = $token;
		}
		$this->username = $username;
    }

 
    /**
     * Perform generic API calling
     *
     * This is the base method for all IssueTracker API calls.
     * 
     * @param string $methodURL - IssueTracker method URL
     * @param array $data - optional POST form data
     * @param array $attachemnts - optional list of file paths 
     * @param bool $update - flag to indicate if this is a PUT operation
     *
     * @return string - the JSON string returned from server
     */
    public function API($methodURL, array $data = null, array $attachments = null, $update = false) {
	$url = $this->baseURL . $methodURL;
	try {
	    $curl = curl_init($url); 
	    curl_setopt($curl, CURLOPT_HEADER, false);
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	    curl_setopt($curl, CURLOPT_USERPWD, $this->username . ':' . $this->token);
	   
	    if ($data || $attachments) {
		if ($update) {
		    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
		} else {
		    curl_setopt($curl, CURLOPT_POST, true);
		}
		if ($attachments) {
		    foreach ($attachments as $key => $value) {
			$data['attachment-' . $key] = '@' . $value;
		    }
		}
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
	    }

	    $result = curl_exec($curl);
	    curl_close($curl);
	    return $result;

	} catch (Exception $e) {
	    return $e->Message();
	}
    }

    /**
     * Get all Projects with API enabled
     *
     * @param book $loadWithIssues: Passing true will deep load all of the 
     * projects as well as all of their active issues. 
     *
     * @return string - the JSON string returned from server
     */
    public function getProjects($loadWithIssues = false) {
	$url = $loadWithIssues ? 'Projects/true' : 'Projects';
	return $this->API($url);
    }

    /**
     * Get priority levels
     *
     * @return string - the JSON string returned from server
     */
    public function getPriorityLevels() {
	return $this->API('PriorityLevels');
    }

    /**
     * Get all people in a project
     *
     * @param int $projectID: project id
     *
     * @return string - the JSON string returned from server
     */
    public function getAllPeopleInProject($projectID) {
	return $this->API('PeopleInProject/' . $projectID);
    }

    /**
     * Get all issues in a project
     *
     * @param int $projectID: project id
     *
     * @return string - the JSON string returned from server
     */
    public function getAllIssuesInProject($projectID) {
	return $this->API('IssuesInProject/' . $projectID);
    }

    /**
     * Check if an issue exists
     *
     * @param int $projectID: project id
     * @param string $title: required $title.
     *
     * @return string - the JSON string returned from server
     */
    public function doesIssueExist($projectID, $issueID) {
	return $this->API("DoesIssueExist/{$projectID}/{$issueID}");
    }

    /**
     * Get potential statuses for issue
     *
     * Note: If you are an admin, you'll get both all allowed statuses
     * as well as ALL statuses back from the server
     *
     * @param int $projectID: project id
     * @param string $title: required $title.
     *
     * @return string - the JSON string returned from server
     */
    public function getPotentialStatusesForIssue($projectID, $issueID) {
	return $this->API("PotentialStatusesForIssue/{$projectID}/{$issueID}");
    }

    /**
     * Get issue details
     *
     * Note: You can use this to check if an issue exists as well,
     * since it will return a 404 if the issue does not exist
     *
     * @param int $projectID: project id
     * @param string $title: required $title.
     *
     * @return string - the JSON string returned from server
     */
    public function getIssueDetails($projectID, $issueID) {
	return $this->API("Issue/{$projectID}/{$issueID}");
    }

    /**
     * Get a list of people that can be assigend to an issue
     *
     * @param int $projectID: project id
     * @param string $title: required $title.
     *
     * @return string - the JSON string returned from server
     */
    public function getPeopleForIssueAssignment($projectID, $issueID) {
	return $this->API("PeopleForIssueAssignment/{$projectID}/{$issueID}");
    }

    /**
     * Create Issue
     *
     * @param int $projectID: project id
     * @param string $title: required $title.
     * @param int $priorityID: priority levels.
     * @param int $resolverID: person assigned to solve this issue.
     * @param int $testerID: person assigned to test and verify if a issue is
     *   resolved.
     * @param string $description: optional description of the issue.
     * @param string $tags: a string of tags delimited by comma.
     * @param string $watcherIDs: a string of people's id delimited by comma.
     * @param array $attachment: list of file paths
     *
     * @return string - the JSON string returned from server
     */
    public function createIssue(
	$projectID, $title, $priorityID, $resolverID, $testerID,
	$description = null,  $tags = null, $watcherIDs = null,
	$attachments = null) {
	$data = array(
	    'title' => $title,
	    'priority_level_id' => $priorityID,
	    'resolver_id' => $resolverID,
	    'tester_id' => $testerID,
	);
	if ($description) {
	    $data['description'] = $description;
	}
	if ($tags) {
	    $data['tags'] = $tags;
	}
	if ($watcherIDs) {
	    $data['watcher_id'] = $watcherIDs;
	}
	return $this->API("Issue/{$projectID}", $data, $attachments);
    }

    /**
     * Create Comment on issue
     *
     * @param int $projectID: project id
     * @param int $issueID: issue id
     * @param string $comment: comment string
     * @param string $peopleToCCIDs: a string of people to be CCed on this comment,
     *   delimited by comma.
     * @param array $attachments: list of file paths
     *
     * @return string - the JSON string returned from server
     */
    public function createComment(
	$projectID, $issueID, $comment,
	$peopleToCCIDs = null, $attachments = null) {
	$data = array('comment' => $comment);
	if ($peopleToCCIDs) {
	    $data['people_to_cc_ids'] = $peopleToCCIDs;
	}
	return $this->API("Comment/{$projectID}/{$issueID}", $data, $attachments);
    }

    /**
     * Update Issue
     *
     * If you provide any parameters then the value you pass will be
     * used to update the issue. If you wish to keep the value that's
     * already on an issue, then do not provide the parameter in your
     * PUT data. Any value you provide, including tags, will overwrite
     * the existing values on the issue. If you wish to retain the
     * tags for an issue and update it by adding one new tag, then
     * you'll have to provide all of the existing tags as well as the
     * new tag in your tags parameter, for example.
     *
     * @param int $projectID: project id
     * @param int $issueID: issue id
     * @param string $title: required $title
     * @param int $priorityID: priority levels
     * @param int $resolverID: person assigned to solve this issue
     * @param int $testerID: person assigned to test and verify if a issue is
     * resolved
     * @param string $description: optional description of the issue
     * @param string $tags: a string of tags delimited by comma
     * @param string $stateID: a valid state that this issue can transition to
     * @param array attachments: list of file paths
     *
     * @return string - the JSON string returned from server
     */
    public function updateIssue(
        $projectID, $issueID, $title = null, $priorityID = null,
        $resolverID = null, $testerID = null, $description = null, 
	$tags = null, $stateID = null, array $attachments = null) {

	$data = array();
	if ($title) {
	    $data['title'] = $title;
	}
	if ($priorityID) {
	    $data['priority_level_id'] = $priorityID;
	}
	if ($resolverID) {
	    $data['resolver_id'] = $resolverID;
	}
	if ($testerID) {
	    $data['tester_id'] = $testerID;
	}
	if ($description) {
	    $data['description'] = $description;
	}
	if ($tags) {
	    $data['tags'] = $tags;
	}
	if ($stateID) {
	    $data['state_id'] = $stateID;
	}
	return $this->API("Issue/{$projectID}/{$issueID}", $data, $attachments, true);
    }
}
