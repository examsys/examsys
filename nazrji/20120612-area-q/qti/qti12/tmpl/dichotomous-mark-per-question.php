<?php require("header.php"); ?>
			<qticomment>Marking:<?php echo $question->score_method ?></qticomment>
		
			<?php echo $headertext ?>

<?php foreach ($question->options as $oid => $option) : ?>
			<response_lid ident="<?php echo $oid ?>">
				<material>
					<mattext texttype="text/html"><![CDATA[<?php echo $option->text ?>]]></mattext>
<?php if ($option->media) : ?>
					<matimage imagtype="<?php echo $option->media_type ?>" uri="<?php echo $option->media ?>"/>
<?php endif; ?>
				</material>	
				<render_choice shuffle="No">
					<response_label ident="A">
						<material>
							<mattext><?php echo $true ?></mattext>
						</material>
					</response_label>	
					<response_label ident="B">
						<material>
							<mattext><?php echo $false ?></mattext>
						</material>
					</response_label>	
				</render_choice>
			</response_lid>
<?php endforeach; ?>		
		</presentation>
		
		
		
		<resprocessing>
			<outcomes>
				<decvar/>
			</outcomes>

			<!-- force general feedback to output -->
			<respcondition title="general checked" continue="Yes">
				<conditionvar>
					<varequal respident="1">A</varequal>
				</conditionvar>
				<setvar action='Add'>0</setvar>
				<displayfeedback linkrefid="general"/>
			</respcondition>
			<respcondition title="general unchecked" continue="Yes">
				<conditionvar>
					<not>
						<varequal respident="1">A</varequal>
					</not>
				</conditionvar>
				<setvar action='Add'>0</setvar>
				<displayfeedback linkrefid="general"/>
			</respcondition>
			
			<!-- response conditions. 2 per option with checked and unchecked -->
<?php foreach ($question->options as $oid => $option) : ?>
      <?php //echo "\n\n\n|" . $option->iscorrect . "|\n\n\n"; ?>
			
      
      <?php if($option->iscorrect) : ?>
      
      <respcondition title="<?php echo $oid ?> <?php echo(for_id($option->text)) ?> True" continue="Yes">
				<conditionvar>
					<varequal respident="<?php echo $oid ?>">A</varequal>
				</conditionvar>
        <setvar action='Set'><?php echo $option->marks_correct;?></setvar>  
				<displayfeedback linkrefid="<?php echo $oid ?> <?php echo(for_id($option->text)) ?> True"/>
			</respcondition>
      
			<respcondition title="<?php echo $oid ?> <?php echo(for_id($option->text)) ?> False" continue="NO">
				<conditionvar>
					<varequal respident="<?php echo $oid ?>">B</varequal>
				</conditionvar>
        <setvar action='Set'><?php echo $option->marks_incorrect;?></setvar>  
				<displayfeedback linkrefid="<?php echo $oid ?> <?php echo(for_id($option->text)) ?> False"/>
			</respcondition>
      
      <?php else : ?>
      
      <respcondition title="<?php echo $oid ?> <?php echo(for_id($option->text)) ?> True" continue="NO">
				<conditionvar>
					<varequal respident="<?php echo $oid ?>">A</varequal>
				</conditionvar>
        <setvar action='Set'><?php echo $option->marks_incorrect;?></setvar>  
				<displayfeedback linkrefid="<?php echo $oid ?> <?php echo(for_id($option->text)) ?> True"/>
			</respcondition>
      
			<respcondition title="<?php echo $oid ?> <?php echo(for_id($option->text)) ?> False" continue="Yes">
				<conditionvar>
					<varequal respident="<?php echo $oid ?>">B</varequal>
				</conditionvar>
        <setvar action='Set'><?php echo $option->marks_correct;?></setvar>  
				<displayfeedback linkrefid="<?php echo $oid ?> <?php echo(for_id($option->text)) ?> False"/>
			</respcondition>
      
      <?php endif ?>
       
<?php endforeach; ?>
		</resprocessing>
	
		<!-- feedback items for each item, pick right feedback based on correct or incorrect -->

<?php foreach ($question->options as $oid => $option) : ?>
<?php if ($option->iscorrect) {
    $fb = $option->fb_correct;
  } else {
    $fb = $option->fb_incorrect;
  }
?>
		<itemfeedback ident='<?php echo $oid ?> <?php echo(for_id($option->text)) ?> True' view='Candidate'>
			<material>
				<mattext texttype='text/html'><![CDATA[<?php echo $fb ?>]]></mattext>
			</material>
		</itemfeedback>			
<?php if (!$option->iscorrect) {
    $fb = $option->fb_correct;
  } else {
    $fb = $option->fb_incorrect;
  }
?>
		<itemfeedback ident='<?php echo $oid ?> <?php echo(for_id($option->text)) ?> False' view='Candidate'>
			<material>
				<mattext texttype='text/html'><![CDATA[<?php echo $fb ?>]]></mattext>
			</material>
		</itemfeedback>	
				
<?php endforeach; ?>

		<!-- general feedback -->
		<itemfeedback ident='general' view='Candidate'>
			<material>
				<mattext texttype='text/html'><![CDATA[<?php echo $question->feedback ?>]]></mattext>
			</material>
		</itemfeedback>
	</item>
