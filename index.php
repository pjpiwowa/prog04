<!DOCTYPE html>

<html lang="en">

<head>
	<title>SVSU Course Listing</title>

	<style>
		body
		{
			max-width: 80%;
			margin: auto;
		}
		form
		{
			max-width: 20em;
		}
	</style>
</head>

<body>
<h1>SVSU Courses</h1>

<?php

function filter_courses(/* array */ $courses)
{
	$ret = array();
}

$api_base = "http://api.svsu.edu/courses?";
$api_url = $api_base;

if (!empty($_GET["prefix"]))
{
	$api_url = $api_url . "prefix=" . $_GET["prefix"] . "&";
}

if (!empty($_GET["courseNumber"]))
{
	$api_url = $api_url . "courseNumber=" . $_GET["courseNumber"] . "&";
}

if (!empty($_GET["instructor"]))
{
	$api_url = $api_url . "instructor=" . $_GET["instructor"] . "&";
}

if ($api_base === $api_url)
{
	echo "<p id='courses'>No search terms provided...</p>\n";
}
else
{
	/*
	 * You may see, in PHP error logs, something like the following:
	 *
	 * PHP Warning:  file_get_contents(): http:// wrapper is disabled in the server configuration by allow_url_fopen=0
	 *
	 * This needs to be changed in your php configuration file (php.ini).
	 *
	 * I had further difficulty on my server getting name resolution to work
	 * for php (it runs in a very limited chroot on my setup); I wound up
	 * hardcoding api.svsu.edu's address in /etc/hosts. (It is not possible
	 * to hardcode api.svsu.edu's address in the actual URL, because TLS
	 * negotiation fails, and api.svsu.edu forces TLS. This application does
	 * not need TLS. Apparently OIT feels they know better.)
	 */
	$pile = file_get_contents($api_url);

	if (!empty(pile))
	{
		$cobj = json_decode($pile);
		$courses = $cobj["courses"];

		echo "<h2>Monday</h2>"
		
	}
	else
	{
		echo "<p id='courses'>Hmm... the SVSU API returned no results.</p>";
	}
}

?>

<h2>Course Search</h2>
<form action="index.php" id='search'>
	<label>Prefix (subject):</label>
	<input type='text' placeholder='SUBJ' name='prefix' />
	<hr />

	<label>Number:</label>
	<input type='text' placeholder='999' name='courseNumber' />
	<hr />

	<label>Instructor:</label>
	<input type='text' placeholder='jqdoe' name='instructor' />
	<hr />

	<fieldset>
		<label>Mondays</label>
		<input type='checkbox' name='dow' id='M' /><br />

		<label>Tuesdays</label>
		<input type='checkbox' name='dow' id='T' /><br />

		<label>Wednesdays</label>
		<input type='checkbox' name='dow' id='W' /><br />

		<label>Thursdays</label>
		<input type='checkbox' name='dow' id='R' /><br />

		<label>Fridays</label>
		<input type='checkbox' name='dow' id='F' /><br />

		<label>Saturdays</label>
		<input type='checkbox' name='dow' id='S' /><br />

		<!--
		     I can't figure out whether the (apparently undocumented)
		     SVSU courses API has a lettercode for Sunday, and if so
		     what it is. Sundays do not exist.
		-->
	</fieldset>
	<br />
	
	<input type='submit' />
</form>

</html>
