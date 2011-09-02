	<item title="<?php echo(StripForTitle($title)) ?>" ident="<?php echo $question->load_id ?>">
		<itemmetadata>
			<qmd_itemtype><?php echo $type ?></qmd_itemtype>
			<qmd_status><?php echo $question->status ?></qmd_status>
			<qmd_toolvendor>Touchstone 4.0</qmd_toolvendor>
		</itemmetadata>
		
		<presentation>

<?php if ($question->author):?>
			<qticomment>Author:<?php echo $question->author ?></qticomment>
<?php endif; ?>	
<?php if ($question->q_group):?>
			<qticomment>Module:<?php echo $question->q_group ?></qticomment>
<?php endif; ?>	
<?php if ($question->bloom) : ?>
			<qticomment>Blooms:<?php echo $question->bloom ?></qticomment>
<?php endif; ?>
<?php if (count($question->keywords) > 0) : ?>
<?php foreach ($question->keywords as $keyword): ?>
<?php if (trim($keyword) != ""):?>
			<qticomment>Keyword:<?php echo $keyword ?></qticomment>
<?php endif; ?>
<?php endforeach; ?>
<?php endif; ?>
