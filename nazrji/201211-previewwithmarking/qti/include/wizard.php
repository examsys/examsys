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
 * @copyright Copyright (c) 2011 The University of Nottingham
 * @package
 */

class WizardPage {
  var $id = '';
  var $title = '';
  var $pageno = 0;
  var $grouping = '';
  var $phpfile = '';
}

class Wizard {
  var $pages = array();
  var $pagecount = 0;

  function AddPage($pageid, $pagetitle, $grouping, $phpfile = '') {
    $page = new WizardPage();
    $page->id = $pageid;
    $page->title = $pagetitle;
    $page->pageno = $this->pagecount;
    $page->grouping = $grouping;
    $page->phpfile = $phpfile;

    $this->pages[] = $page;

    $this->pagecount++;
  }

  function Display($startpage = "") {
    if ($startpage == "") $startpage = $this->pages[0]->id;
?>

<table cellspacing="0" cellpadding="0" height="100%" width="100%">
	<tr>
		<td width="195" style="background-color:#D6DFF7" valign="top">
			<div class="wizard_left_head">Import Export Wizard</div>
			<?php $curgrouping = ""; ?>
			<?php foreach ($this->pages as $page) : ?>
			
				<?php if ($page->grouping != $curgrouping) :
        $curgrouping = $page->grouping;
?>
					<div class="wizard_left_grouping"><?php echo $curgrouping; ?></div>
				<?php endif; ?>
				
				<div class="wizard_left_section" id="leftheader_<?php echo $page->id; ?>">
					<img src="artwork/notdone.png" width="18" height="18" id="icon_<?php echo $page->id; ?>">
					<?php echo $page->title; ?>
				</div>
			<?php endforeach; ?>
		</td>
	
		<td valign="top">
		
			<div id="pageheader" class="wizard_header">
				<table cellpadding="0" cellspacing="0" border="0" width="100%">
					<tr>
						<td style="background-color:#EBEADB"><div style="font-size:220%; font-weight:bold">&nbsp;Rog&#333; Import Export Wizard</td>
						<td style="background-color:#EBEADB; text-align:right"><img src="../artwork/black_uon_logo.png" width="151" height="47" alt="Logo" border="0" />&nbsp;&nbsp;</td>
					</tr>
					<tr>
						<td colspan="2" style="height:3px"><img src="./artwork/header_horizontal_line.gif" width="100%" height="3" alt="Line" /></td>
					</tr>
				</table>
			</div>
			<div id="panel" class="wizard_panels">	
				
				<?php foreach ($this->pages as $page) : ?>
							
					<div id="page_<?php echo $page->id; ?>" style="display:none;">
						<div class="wizard_page_title" id="title_<?php echo $page->id; ?>">
							<?php echo $page->title; ?>
						</div>
						<img src="../artwork/divider_bar.gif" width="290" height="1" alt="Divider Bar" />

						<div id="content_<?php echo $page->id; ?>" class="wizard_panel_content">
							<? if ($page->phpfile) : ?>
								<? include($page->phpfile); ?>
							<? endif; ?>
						</div>
					</div>
							
				<?php endforeach; ?>
			</div>
			<div class="wizard_footer">
				<table cellpadding="0" cellspacing="0" border="0" width="100%">
					<tr>
						<td colspan="2" style="height:3px"><img src="./artwork/header_horizontal_line.gif" width="100%" height="3" alt="Line" /></td>
					</tr>
					<tr height="35">
						<td style="background-color:#EBEADB"><div style="font-size:130%; font-weight:bold"></td>
						<td style="background-color:#EBEADB; text-align:right">
							<button onclick='W_PrevPanel()' id="back_button">Back</button>
							<button onclick='W_NextPanel()' id="next_button">Next</button>
						</td>
					</tr>
				</table>
			</div>
		</td>
	</tr>
</table>

<script type="text/javascript">
var Pages = new Array(<?php echo count($this->pages); ?>);

<?php foreach($this->pages as $page): ?>
Pages[<?php echo $page->pageno; ?>] = {id: '<?php echo $page->id; ?>', title: '<?php echo $page->title; ?>'};
<?php endforeach; ?>

W_ShowPanelByName('<?=$startpage?>');
</script>
<?php

  }
}
