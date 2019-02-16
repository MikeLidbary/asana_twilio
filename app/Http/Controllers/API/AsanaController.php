<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Twilio\Rest\Client;
use Asana\Client as TwilioClient;

class AsanaController extends Controller {

	public function notifications( Request $request ) {
		$message = "";
		// create message
		if ( $request->task_id ) {
			$message = "A new task has been created.";
		} else if ( $request->created_by ) {
			$message = $request->created_by . " has commented on " . $request->task_name . ".";
		} else if ( $request->task_completed ) {
			$message = "Task " . $request->task_name . " has been completed.";
		}

		// Your Account SID and Auth Token from twilio.com/console
		$account_sid = getenv( 'ACCOUNT_SID' );
		$auth_token  = getenv( 'TWILIO_TOKEN' );

		// A Twilio number you own with SMS capabilities
		$twilio_number = getenv( 'TWILIO_NUMBER' );

		// List of project members phone numbers
		$numbers = [ 'list of member phone numbers' ];
		$client  = new Client( $account_sid, $auth_token );

		//loop through the numbers
		foreach ( $numbers as $number ) {
			$client->messages->create(
			// Where to send a text message (your cell phone?)
				$number,
				array(
					'from' => $twilio_number,
					'body' => $message
				)
			);
		}

		return "ok";
	}

	/**
	 * create asana task
	 */
	public function index(){

		$asana_access_token = env( 'ASANA_ACCESS_TOKEN' );

		// receive sms sent from Twilio
		$sms_body = $_REQUEST[ 'Body' ];
		$keywords = preg_split( "/due on/i", $sms_body );

		// create a $client->with a Personal Access Token
		$client = TwilioClient::accessToken( $asana_access_token );
		$me     = $client->users->me();

		// find your "Personal Projects" project
		$workspace = array_filter( $me->workspaces, function( $item ) {
			return $item->name === 'Personal Projects';
		} );

		$personal_projects = array_pop( $workspace );
		$projects          = $client->projects->findByWorkspace( $personal_projects->id, null, [ 'iterator_type' => false, 'page_size' => null ] )->data;

		// create a project if it doesn't exist
		$workspace_project = array_filter( $projects, function( $project ) {
			return $project->name === 'Example';
		} );

		$project = array_pop( $workspace_project );

		if ( $project === null ) {
			echo "creating 'demo project'\n";
			$project = $client->projects->createInWorkspace( $personal_projects->id, [ 'name' => 'Example' ] );
		}

		// create a task in the project
		$demoTask = $client->tasks->createInWorkspace( $personal_projects->id, [
			"name"     => $keywords[0],
			"due_on"   => $keywords[1],
			"projects" => array( $project->id )
		] );

		echo "Task " . $demoTask->id . " created.\n";
	}
}
