<div id="divAddModifyService">
 <div class="hd"></div>
 <div class="bd">
<form name="formAddModifyService" method="POST" action="/api/w/v2/addmodify_service.php">
<input type="hidden" name="service_id">
<table width="99%">
 <tr>
  <td><label for="service_name">Service</label></td>
  <td><input type="text" name="service_name" size="30"></td>
 </tr>
 <tr>
  <td><label for="enabled">Enabled:</label></td>
  <td>
<select name="enabled">
 <option value="null"></option>
 <option value="0">0</option>
 <option value="1">1</option>
</select>
  </td>
 </tr>
 <tr>
  <td><label for="notifications">Notifications:</label></td>
  <td>
<select name="notifications">
 <option value="null"></option>
 <option value="0">0</option>
 <option value="1">1</option>
</select>
  </td>
 </tr>
 <tr>
  <td><label for="priority">Priority</label></td>
  <td><input type="text" name="priority" size="6"></td>
 </tr>
 <tr>
  <td><label for="check_interval">Interval</label></td>
  <td><input type="text" name="check_interval" size="6"> Minutes</td>
 </tr>
 <tr>
  <td><label for="check_attempts">Attempts</label></td>
  <td><input type="text" name="check_attempts" size="6"> <span class="form_tip">(Alert after this many non-zero checks)</span></td>
 </tr>
 <tr>
  <td><label for="warning_threshold">Warning</label></td>
  <td><input type="text" name="warning_threshold" size="30"></td>
 </tr>
 <tr>
  <td><label for="critical_threshold">Critical</label></td>
  <td><input type="text" name="critical_threshold" size="30"></td>
 </tr>
 <tr>
  <td><label for="args">Args</label></td>
  <td><input type="text" name="args" size="30"></td>
 </tr>
 <tr>
  <td><label for="wiki">Wiki</label></td>
  <td><input type="text" name="wiki" size="30"></td>
 </tr>
 <tr>
  <td><label for="check_command">Command</label></td>
  <td><select name="check_command">
<?php
list($check_commands, $junk) = $mon->listCheckCommands();
foreach($check_commands as $command) {
	if($command['type'] != '') {
		 printf("<option value=\"%s\">%s (%s)</option>", $command['check_command'], $command['check_command'], $command['type']);
	} else {
		 printf("<option value=\"%s\">%s</option>", $command['check_command'], $command['check_command']);
	}
}
?>
   </select>
  </td>
 </tr>
 <tr>
  <td><label for="description">Description</label></td>
  <td><textarea name="description" rows="10" cols="20"></textarea></td>
 </tr>
</table> 
</form>
 </div>
</div>
