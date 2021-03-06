<?php

function RenderMessages(&$messageData){
	$render = Array();
	AddActionLog("RenderMessages");
	StartTimer("RenderMessages");

	foreach($messageData->MessageModels as $i => $messageModel){
		$message = Array();
		
		$message["message_type"] = trim($messageModel->Type);
		$message["message_title"] = trim($messageModel->Title);
		$message["message_body"] = trim($messageModel->Body);

		$render[] = $message;
	}

	StopTimer("RenderMessages");
	return $render;
}

?>