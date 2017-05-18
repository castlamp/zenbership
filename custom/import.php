<?php

/**
 * Member/Contact Import Tool
 *
 * Upload this and your CSV file to the "custom" folder
 * and run it from a web browser.
 */

/*
TRUNCATE ppSD_members;
TRUNCATE ppSD_member_data;
*/

// Server, please don't cause us issues!
ini_set('memory_limit', '2400M');
ini_set('max_execution_time', '10000');

// Zenbership Environment
require "../admin/sd-system/config.php";
$note = new notes();

// --------------------------------------------------
// --------------------------------------------------
// Options

// member or contact
$importType			= 'member';

$file				= 'seed_data.csv';

$start_on_line 		= 0; // 40892

// Column name in CSV => Zenbership Name
$dateFields			= array(
	'joined' 			=> 'joined', // member
	'created'			=> 'created', // contact
	'last_renewal' 		=> 'last_renewal',
	'last_login' 		=> 'last_login',
	'last_action' 		=> 'last_action',
	'next_action' 		=> 'next_action',
);

// This field on the import file will be created
// as a note rather than a field in the DB.
$notesField			= '';
$notesDivider		= "";
$noteLabel 			= '1';

// --------------------------------------------------
// --------------------------------------------------

?><html>
<head>
<style type="text/css">
body {
	font-family: 'courier', 'courier new', 'arial';
	font-size: 11px;
	margin: 40px;
	padding: 0px;
}
</style>
<script type="text/javascript">
	var scrollInterval = setInterval(function() { 
	    document.body.scrollTop = document.body.scrollHeight;
	}, 50);

	var stopScroll = function() {
		clearInterval(scrollInterval);
	};
</script>
</head>

<body>

<?php

if (file_exists($file)) {

	if ($importType == 'contact') {
		$user = new contact();
	} else {
		$user = new user();
	}

	$entries 			= 0;
	$insert_line 		= '';
	$errors 			= array();

	if(($handle = fopen($file, 'r')) !== false)
	{
	    $header = fgetcsv($handle);

	    // Get the headers from the first line.
	    foreach ($header as $item) {
	    	$insert_line .= ",'" . $item . "'";
	    }
	    $insert_line = ltrim($insert_line, ",");

	    // Loop the CSV file.
	    while(($data = fgetcsv($handle)) !== false)
	    {
	    	$entries++;

	    	$holdNotes = '';

	    	if ($entries >= $start_on_line) {

		    	$userData = array_combine($header, $data);

		    	// --------------------------------------------------
		    	// Date handling.

		    	foreach ($dateFields as $csvKey => $zenKey) {
			    	if (! empty($userData[$csvKey])) {
		    			$userData[$zenKey] = date('Y-m-d', strtotime($userData[$csvKey])) . ' 00:00:00';
		    			unset($userData[$csvKey]);
			    	}
		    	}

		    	// --------------------------------------------------
		    	// Notes?

		    	if (! empty($notesField) && ! empty($userData[$notesField])) {
		    		$holdNotes = $userData[$notesField];
		    		unset($userData[$notesField]);
		    	}

		    	// --------------------------------------------------
		    	// Run any custom logic for this import.

		    	if (file_exists('import_special_rules.php')) {
		    		include 'import_special_rules.php';
		    	}

		    	// --------------------------------------------------
		    	// Perform the import of this record.

				try {
					if ($importType == 'contact') {
		    			$create = $user->create($userData);
		    			$memId = $create['id'];
					} else {
		    			$create = $user->create_member($userData, '1');
		    			$memId = $create['member_id'];
					}

		    		// Create notes, if any.
		    		if (! empty($holdNotes)) {
		    			$allNotes = explode($notesDivider, $holdNotes);
		    			foreach ($allNotes as $aNote) {
					        $noteId = $note->add_note(array(
					            'label' => $noteLabel,
					            'user_id' => $memId,
					            'item_scope' => $importType,
					            'name' => 'Imported Note',
					            'note' => trim($aNote),
					            'added_by' => '1',
					        ));
		    			}
		    		}
				}
				catch (PDOException $e) {
					$errors[] = $data;
				}

		    	// --------------------------------------------------
		    	// Success of failed?

		    	if (! empty($memId)) {
		    		if ($importType == 'member') {
		    			echo "<li>#" . $entries . ": " . $create['member']['data']['username'];
		    		} else {
		    			echo "<li>#" . $entries . ": " . $memId . ": " . $data['last_name'] . ', ' . $data['first_name'];
		    		}
		    	} else {
		    		echo "<li style=\"color:red;\">#" . $entries . ": Error: " . $data['last_name'] . ', ' . $data['first_name'] . ' -- ' . $data['email'];
		    	}

	    	} else {
		    		echo "<li style=\"color:gray;\">#" . $entries . ": Skipped: " . $data['last_name'] . ' ' . $data['first_name'] . ' -- ' . $data['email'];
	    	}

	        unset($data);
	    }

	    fclose($handle);
	}

	echo "<hr /><h1>Errors Reported</h1>";
	var_dump($errors);
	echo "<hr />";
	echo "<h1>Done</h1>" . $entries . ".";
	echo "<script>stopScroll();</script>";

} else {

	echo '<h1>Error</h1><p>Import file not found. Please upload it and try again.</p>';

}

?>

</body>
</html>