<?php
define('QUADODO_IN_SYSTEM', true);
require_once '../includes/header.php';

if($_SERVER['REQUEST_METHOD'] == 'POST'){
	require_once '../includes/Validate.class.php';
	
	$validate = new Validate($qls);
	$validate->returnData['success'] = array();

	if ($validate->returnData['active'] == 'inactive') {
		echo json_encode($validate->returnData);
		return;
	}
	
	$data = json_decode($_POST['data'], true);
	validate($data, $validate, $qls);
	
	if (!count($validate->returnData['error'])){
		if($data['action'] == 'create') {
			$qls->Security->check_auth_page('administrator.php');
			
			// Super secret code so they can register
			$code = sha1(md5($qls->config['sql_prefix']) . time() . $_SERVER['REMOTE_ADDR']);
			$subject = "You've been invited!";
			$recipientEmail = $data['email'];
			
			if($qls->config['cookie_domain'] == '') {
				$domain = $_SERVER['SERVER_ADDR'];
			} else {
				$domain = $qls->config['cookie_domain'];
			}
			
			if($qls->config['cookie_path'] == '') {
				$appPath = '/';
			} else {
				$appPath = $qls->config['cookie_path'];
				if(substr($appPath, -1) != '/') {
					$appPath .= '/';
				}
			}

			$btnURL = $domain.$appPath.'register.php?code='.$code;
			$btnText = 'Accept Invitation';
			
			$qls->SQL->insert('invitations',
				array(
					'email',
					'to_id',
					'from_id',
					'code',
				),
				array(
					$recipientEmail,
					0,
					$qls->user_info['id'],
					$code,
				)
			);

			$msg = file_get_contents('../html/email_invitation.html');
			$msg = str_replace('<!--btnURL-->', $btnURL, $msg);
			$msg = str_replace('<!--btnText-->', $btnText, $msg);
			
			//$qls->PHPmailer->SMTPDebug = 3;
			$qls->PHPmailer->addAddress($recipientEmail, '');
			$qls->PHPmailer->Subject = $subject;
			$qls->PHPmailer->msgHTML($msg);
			if(!$qls->PHPmailer->send()) {
				array_push($validate->returnData['error'], $qls->PHPmailer->ErrorInfo);
			} else {
				$validate->returnData['success'] = 'Invitation sent to: '.$recipientEmail;
			}
			$qls->PHPmailer->clearAllRecipients();
		}
	}
	echo json_encode($validate->returnData);
}

function validate($data, &$validate, &$qls){
	$error = [];
	
	return $error;
}

?>
