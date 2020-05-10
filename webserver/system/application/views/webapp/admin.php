<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8"> 
		<title></title>
	</head>
	<body>
	
	<div id="wrapper">

		<form>
			<h1>General</h1>
			<fieldset>
			<label>"Live" mode</label><br />
			<input type="radio" name="liveValue" value="yes">Yes
			<input type="radio" name="liveValue" value="yes">No<br />
			<br />
			<label>Event</label><br />
			<input type="text" name="eventNameField">
			<br />
			<label>MMS Message</label><br />
			<input type="text" name="mmsMessageField">
			<br />
			<label>Email Message</label><br />
			<input type="text" name="emailMessageField">
			<br />
			<label>Twitter Message</label><br />
			<input type="text" name="twitterMessageField">
			</fieldset>
			
			<h1>Auto-Refresh Settings</h1>
			<fieldset>
			<label>Auto-Refresh</label><br />
			<input type="radio" name="refreshValue" value="yes">Yes
			<input type="radio" name="refreshValue" value="yes">No<br />
			<br />
			<label>Seconds between refreshes</label><br />
			<input type="text" name="refreshSecondsField">
			<br />
			<label>Max Photos</label><br />
			<input type="text" name="maxPhotosField">
			</fieldset>
			
			<h1>Server Settings</h1>
			<fieldset>
			<label>SmileServer IP</label><br />
			<input type="text" name="serverField">
			<br />
			<label>Folder</label><br />
			<input type="text" name="folderField">
			</fieldset>

		</form>
	
	</div><!--end #wrapper-->
	</body>
</html>
