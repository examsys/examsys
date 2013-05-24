<?php
require_once '../include/load_config.php';
$deb32434=1;
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

$configObject=Config::get_instance();
$cfg_web_root=$configObject->get('cfg_web_root');
?>
	<item title="<?php echo(StripForTitle($title)) ?>" ident="<?php echo $question->load_id ?>">
		<itemmetadata>
			<qmd_itemtype><?php echo $type ?></qmd_itemtype>
			<qmd_status><?php echo $question->status ?></qmd_status>
			<qmd_score_method><?php echo $question->score_method ?></qmd_score_method>
			<qmd_toolvendor>Rogo <?php echo $configObject->get('rogo_version'); ?></qmd_toolvendor>
		</itemmetadata>
		
		<presentation>

<?php if ($question->author) : ?>
			<qticomment>Author:<?php echo $question->author ?></qticomment>
<?php endif; ?>	
<?php if ($question->q_group) : ?>
			<qticomment>Module:<?php echo $question->q_group ?></qticomment>
<?php endif; ?>	
<?php if ($question->bloom) : ?>
			<qticomment>Blooms:<?php echo $question->bloom ?></qticomment>
<?php endif; ?>
<?php if (count($question->keywords) > 0) : ?>
<?php foreach ($question->keywords as $keyword) : ?>
<?php if (trim($keyword) != "") : ?>
			<qticomment>Keyword:<?php echo $keyword ?></qticomment>
<?php endif; ?>
<?php endforeach; ?>
<?php endif; ?>
