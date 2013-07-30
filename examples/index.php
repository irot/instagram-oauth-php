<?php

require "./includes/instagramoauth.class.php";

$instagram = new InstagramOAuth(
	"[YOUR CLIENT ID]",
	"[YOUR CLIENT SECRET]",
	"[YOUR REDIRECT URL]",
	array(
		InstagramOAuth::PERMISSION_COMMENTS,
		InstagramOAuth::PERMISSION_LIKES,
		InstagramOAuth::PERMISSION_RELATIONSHIPS)
);

if ($instagram->isReady()) {
	// GET USER INFO - NO EXTRA API CALLS NEEDED
	print "<pre>" . htmlspecialchars(print_r($instagram->getUserInfo(), TRUE)) . "</pre>";

	// GET A LITTLE MORE USER INFO
	$instagram->get("users/self");
	print "<pre>" . htmlspecialchars(print_r($instagram->response(), TRUE)) . "</pre>";

	// GET PROFILE PICTURE
	/*header("Content-Type: image/jpeg");
	$url = $instagram->getUserInfo("profile_picture");
	$instagram->getImage($url);

	$image_p = imagecreatetruecolor(150, 150);
	$image_j = imagecreatefromstring($instagram->response());
	imagecopyresampled($image_p, $image_j, 0, 0, 0, 0, 150, 150, 150, 150);
	imagejpeg($image_p, NULL, 100);
	imagedestroy($image_p);*/
}