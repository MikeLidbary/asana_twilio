<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Twilio\Rest\Client;

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

}
