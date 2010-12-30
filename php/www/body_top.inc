<table id="tableBodyTop" width="99%" border="0">
 <tr><td width="30%">
  <a href="/"><img border="0" src="/img/bg_logo_transparent.png"></a>
 </td><td id="tdStatusUpdate">
 </td>
 <td align="right" width="30%">
 </td></tr>
</table>

<?php
$main_tabs = array(
	'/status' => 'Status',
	'/nodegroups' => 'Nodegroups',
	'/nodes' => 'Nodes',
	'/services' => 'Services',
	'/docs' => 'Docs',
);
$www->showMainTabs($main_tabs, '/status');
?>
