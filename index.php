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
		table
		{
			border-collapse: collapse;
		}
		td
		{
			border: 1px solid black;
			padding: 0.5em;
		}
		td td
		{
			border: 1px dotted gray;
		}
	</style>
</head>

<body>
<h1>SVSU Courses</h1>

<a href="https://github.com/pjpiwowa/prog04">Source Code</a>

<?php

function indent(/* int */ $n, /* string */ $s)
{
	$ret = "";
	for ($index = 0; $index < $n; $index += 1)
	{
		$ret = $ret . "\t";
	}
	return $ret . $s;
}

function filter_courses_by_day(/* array */ $courses, /* array of string */ $days)
{
	$ret = array();
	foreach ($courses as $course)
	{
		foreach ($course->meetingTimes as $time)
		{
			foreach ($days as $day)
			{
				if(strstr($time->days, $day))
				{
					array_push($ret, $course);
					break;
				}
			}
		}
	}
	return $ret;
}

function course_table(/* array */ $courses)
{ ?>
<table id='courses'>
	<thead>
		<tr>
			<th>Course</th>
			<th>Description</th>
			<th>Seats (Available/Total)</th>
			<th>Meeting Times</th>
			<th>Instructor(s)</th>
		</tr>
	</thead>
	<tbody>
<?php

	// Convenience macros
	function row_start()
	{
		return indent(2, "<tr>\n");
	}

	function row_end()
	{
		return indent(2, "</tr>\n");
	}

	function item_start()
	{
		return indent(3, "<td>");
	}

	function item_end()
	{
		return "</td>\n";
	}

	foreach ($courses as $course)
	{
		echo row_start();

		echo item_start();
		echo $course->prefix . $course->courseNumber . "*" . $course->lineNumber;
		echo item_end();

		echo item_start();
		echo $course->title;
		echo item_end();

		echo item_start();
		echo $course->seatsAvailable . "/" . $course->capacity;
		echo item_end();

		echo item_start();
		echo "<table><tr>";
		foreach ($course->meetingTimes as $time)
		{
			echo "<td>";
			echo $time->method . ": " . $time->days . ": " . $time->startTime . " - " . $time->endTime . ", " . $time->dates . " @ " . $time->building . $time->room;
			echo "</td>";
		}
		echo "</tr></table>";
		echo item_end();

		echo item_start();
		foreach ($course->instructors as $instructor)
		{
			echo $instructor->username . " ";
		}
		echo item_end();

		echo row_end();
	}

	?>
	</tbody>
</table>
<?php }

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

	if (!empty($pile))
	{
		$cobj = json_decode($pile);
		$courses = $cobj->courses;

		$days = array();

		if (isset($_GET['M']))
		{
			array_push($days, "M");
		}
		if (isset($_GET['T']))
		{
			array_push($days, "T");
		}
		if (isset($_GET['W']))
		{
			array_push($days, "W");
		}
		if (isset($_GET['R']))
		{
			array_push($days, "R");
		}
		if (isset($_GET['F']))
		{
			array_push($days, "F");
		}
		if (isset($_GET['S']))
		{
			array_push($days, "S");
		}

		if (!empty($days))
		{
			$filtered_courses = filter_courses_by_day($courses, $days);
			if (!empty($filtered_courses))
			{
				course_table($filtered_courses);
			}
			else
			{
				echo "<p id='courses'>No courses meeting on specified day(s).</p>\n";
			}
		}
		else
		{
			echo "<p id='courses'>No days selected...</p>\n";
		}
	}
	else
	{
		echo "<p id='courses'>Hmm... the SVSU API returned no results.</p>\n";
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
		<input type='checkbox' name='M' id='M' /><br />

		<label>Tuesdays</label>
		<input type='checkbox' name='T' id='T' /><br />

		<label>Wednesdays</label>
		<input type='checkbox' name='W' id='W' /><br />

		<label>Thursdays</label>
		<input type='checkbox' name='R' id='R' /><br />

		<label>Fridays</label>
		<input type='checkbox' name='F' id='F' /><br />

		<label>Saturdays</label>
		<input type='checkbox' name='S' id='S' /><br />

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
