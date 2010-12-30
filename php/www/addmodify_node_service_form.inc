<div id="divAddModifyServiceNode">
 <div class="hd"></div>
 <div class="bd">
<form name="formAddModifyServiceNode" method="POST" action="/api/w/v2/addmodify_service_node.php">
<table width="99%">
 <tr>
  <td><label for="service_id">Service ID</label></td>
  <td><input type="text" name="service_id" size="6"></td>
 </tr>
 <tr>
  <td><label for="node">Node</label></td>
  <td><input type="text" name="node" size="30"></td>
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
</table> 
</form>
 </div>
</div>
