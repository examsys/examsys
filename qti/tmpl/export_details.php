<?php
// This file is part of Rogō
//
// Rogō is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogō is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogō.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * @author Adam Clarke
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */
require_once '../include/staff_auth.inc';
?>
<html>
<head>
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
	<title>Export to QTI</title>

	<script type="text/javascript" src="./qti/js/mootools-1.2.4.js"></script>
	
	<style type="text/css">
		body {background-color:white; color:black; font-family:Arial,sans-serif;margin:0px;}
		.divider {padding-left:6px; font-weight:bold}
		a {color:black}
		a:hover {color:blue}
		.f {float:left; width:375px; padding-left:12px; font-size:80%}
		.recent {color:blue; font-size:90%}
		.param_section {margin:16px;padding:6px;border: 1px solid #dddddd;}
    td {vertical-align:top}
	</style>
</head>

<body>

<table cellpadding="0" cellspacing="0" border="0" width="100%" style="font-size:90%">
<tbody>
<tr style="background-color:#295AAD; color:white">
	<td style="width:20px">&nbsp;</td>
	<td style="width:20px">&nbsp;</td>
	<td style="width:50%">Question</td>
	<td style="width:55px">ID</td>
	<td style="width:100px">Type</td>
	<td>Details</td>
</tr>

<?php if (count($result['load']['data']->papers) > 0) : ?>
<?php $qno = 1; ?>
	<?php foreach ($result['load']['data']->papers as & $paper) : ?>
		<?php if (count($result['load']['data']->papers) > 1) : ?>
			<tr><td colspan="6" class="divider" style="font-size:120%">Paper <?php echo $paper->paper_title ?></td></tr>
			<tr><td colspan="6" style="height:5px"><hr noshade="noshade" /></td></tr>
		<?php endif; ?>
		<?php foreach ($paper->screens as $s_id => $screen) : ?>
			<tr><td colspan="6" class="divider">Screen <?php echo $s_id ?></td></tr>
			<tr><td colspan="6" style="height:5px"><hr noshade="noshade" /></td></tr>
			<?php foreach ($screen->question_ids as $q_id) : ?>
				<?php $question = FindQuestion($result['load']['data']->questions, $q_id); ?>
					<tr>
						<td></td>
						<td align="right" style="padding-right:6px;"><?php echo $qno ?>.</td>
						<td width="50%"><?php echo(StripForTitle($question->leadin)) ?></td>
						<td><?php echo $question->load_id ?></td>
						<td><nobr>&nbsp;<?php echo(ConvertType($question->type)) ?>&nbsp;</nobr></td>
						<td><?php LogForQuestion($question->load_id) ?></td>
					</tr>	
				<?php $qno++ ?>		
			<?php endforeach; ?>
		<?php endforeach; ?>
	<?php endforeach; ?>

<?php elseif (count($result['load']['data']->questions) > 0) : ?>

<?php $qno = 1; ?>
	<?php foreach ($result['load']['data']->questions as $question) : ?>
		<tr>
			<td></td>
			<td align="right" style="padding-right:6px;"><?php echo $qno ?>.</td>
			<td width="50%"><?php echo(StripForTitle($question->leadin)) ?></td>
			<td><?php echo $question->load_id ?></td>
			<td><nobr><?php echo(ConvertType($question->type)) ?>&nbsp;</nobr></td>
			<td><?php LogForQuestion($question->load_id) ?></td>
		</tr>	
				<?php $qno++ ?>		
	<?php endforeach; ?>

<?php endif; ?>
</table>
</body>
</html>