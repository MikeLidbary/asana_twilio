<?php

namespace App\Http\Controllers;

use Twilio\Rest\Client;

class AutomateController extends Controller {
	/**
	 * send notifications
	 */
	public function notifications() {
		$data = json_decode( file_get_contents( 'php://input' ), true );
		$message = "";
		// create message
		if ( isset( $data['task_id'] ) ) {
			$message = "A new task has been created.";
		} elseif ( isset( $data['created_by'] ) ) {
			$message = $data['created_by'] . " has commented on " . $data['task_name'].".";
		} elseif ( isset( $data['task_completed'] ) ) {
			$message = "Task " . $data['task_name'] . " has been completed.";
		}
		// Your Account SID and Auth Token from twilio.com/console
		$account_sid = getenv( 'ACCOUNT_SID' );
		$auth_token  = getenv( 'TWILIO_TOKEN' );
		// A Twilio number you own with SMS capabilities
		$twilio_number = getenv( 'TWILIO_NUMBER' );
		// List of project members phone numbers
		$numbers = [ 'list of member phone number' ];
		$client  = new Client( $account_sid, $auth_token );
		//loop through the numbers
		foreach ( $numbers as $number ) {
//			dd( $number );
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
