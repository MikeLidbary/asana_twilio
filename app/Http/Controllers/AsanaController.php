<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Asana\Client;

class AsanaController extends Controller
{
	/**
	 * create Asana task
	 */
    public function index(){
	    $ASANA_ACCESS_TOKEN = getenv('ASANA_ACCESS_TOKEN');
	    // receive sms sent from Twilio
	    $sms_body = $_REQUEST['Body'];
	    $keywords = preg_split("/due on/i", $sms_body);
	    // create a $client->with a Personal Access Token
	    $client = Client::accessToken($ASANA_ACCESS_TOKEN);
	    $me = $client->users->me();
	    // find your "Personal Projects" project
	    $personalProjectsArray = array_filter($me->workspaces, function($item) { return $item->name === 'Personal Projects'; });
	    $personalProjects = array_pop($personalProjectsArray);
	    $projects = $client->projects->findByWorkspace($personalProjects->id, null, array('iterator_type' => false, 'page_size' => null))->data;
		// create a project if it doesn't exist
	    $projectArray = array_filter($projects, function($project) { return $project->name === 'Talwork'; });
	    $project = array_pop($projectArray);
	    if ($project === null) {
		    echo "creating 'demo project'\n";
		    $project = $client->projects->createInWorkspace($personalProjects->id, array('name' => 'demo project'));
	    }
	    // create a task in the project
	    $demoTask = $client->tasks->createInWorkspace($personalProjects->id, array(
		    "name" => $keywords[0],
		    "due_on"=>$keywords[1],
		    "projects" => array($project->id)
	    ));
	    echo "Task " . $demoTask->id . " created.\n";
    }
}
