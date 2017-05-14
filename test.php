<?php
	session_start();
	if (!array_key_exists("count", $_SESSION)) {
		$_SESSION["count"] = 0;
	}
	$_SESSION["count"]++;
	echo 'You Have Visit This Page For ' . $_SESSION["count"] . ' Times.';
?>