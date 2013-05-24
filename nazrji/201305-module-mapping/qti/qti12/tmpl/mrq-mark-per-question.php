<?php require("header.php"); ?>

			<?php echo $headertext ?>

			<response_lid ident='1' rcardinality='Multiple'>
				<render_choice shuffle='No' minnumber='<?php echo $maxanswers ?>' maxnumber='<?php echo $maxanswers ?>'>
<?php foreach ($question->options as $oid => $option) : ?>
					<response_label ident='<?php echo $this->ll[$oid] ?>'>
						<material>
							<mattext texttype='text/html'><![CDATA[<?php echo $option->stem ?>]]></mattext>
<?php if ($option->media) : ?>
							<matimage imagtype="<?php echo $option->media_type ?>" uri="<?php echo $option->media ?>"/>
<?php endif; ?>
						</material>
					</response_label>
<?php endforeach; ?>
				</render_choice>
			</response_lid>
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

			<!-- response conditions with no score to display feedback -->
<?php foreach ($question->options as $oid => $option) : ?>
			<respcondition title="<?php echo $oid ?> <?php echo(for_id($option->stem)) ?> checked" continue="Yes">
				<conditionvar>
					<varequal respident="1"><?php echo $this->ll[$oid] ?></varequal>
				</conditionvar>
				<setvar action='Add'>0</setvar>
				<displayfeedback linkrefid="<?php echo $oid ?> <?php echo(for_id($option->stem)) ?> checked"/>
			</respcondition>
			<respcondition title="<?php echo $oid ?> <?php echo(for_id($option->stem)) ?> unchecked" continue="Yes">
				<conditionvar>
					<not>
						<varequal respident="1"><?php echo $this->ll[$oid] ?></varequal>
					</not>
				</conditionvar>
				<setvar action='Add'>0</setvar>
				<displayfeedback linkrefid="<?php echo $oid ?> <?php echo(for_id($option->stem)) ?> unchecked"/>
			</respcondition>
<?php endforeach; ?>

			<!-- marking response stuff -->
			<respcondition title='unanswered' continue="NO" >
        <conditionvar>
<?php foreach ($question->options as $oid => $option) : ?>
					<unanswered respident='1'><?php echo $this->ll[$oid] ?></unanswered>
<?php endforeach; ?>
				</conditionvar>
				<setvar action='Set' >0</setvar>
			</respcondition>

<?php foreach ($question->options as $oid => $option) : ?>
<?php if ($option->is_correct) : ?>
     <respcondition title='Wrong <?php echo $oid; ?>' continue="No" >
        <conditionvar>
         <not>
					<varequal respident='1'><?php echo $this->ll[$oid] ?></varequal>
         </not>
        </conditionvar>
				<setvar action='Set' ><?php echo $option->marks_incorrect; ?></setvar>
			</respcondition>
<?php endif; ?>
<?php endforeach; ?>

			<respcondition title='Right' continue="Yes" >
        <conditionvar>
<?php foreach ($question->options as $oid => $option) : ?>
<?php if ($option->is_correct) : ?>
					<varequal respident='1'><?php echo $this->ll[$oid] ?></varequal>
<?php else : ?>
					<not>
						<varequal respident='1'><?php echo $this->ll[$oid] ?></varequal>
					</not>
<?php endif; ?>
<?php endforeach; ?>
				</conditionvar>
				<setvar action='Set' ><?php echo $option->marks_correct; ?></setvar>
			</respcondition>
      </resprocessing>
		<!-- feedback items for each item -->
<?php foreach ($question->options as $oid => $option) : ?>
<?php if ($option->is_correct) {
    $fb = $option->fb_correct;
  } else {
    $fb = $option->fb_incorrect;
  }
?>
		<itemfeedback ident='<?php echo $oid ?> <?php echo(for_id($option->stem)) ?> checked' view='Candidate'>
			<material>
				<mattext texttype='text/html'><![CDATA[<?php echo $fb ?>]]></mattext>
			</material>
		</itemfeedback>
<?php if (!$option->is_correct) {
    $fb = $option->fb_correct;
  } else {
    $fb = $option->fb_incorrect;
  }
?>
		<itemfeedback ident='<?php echo $oid ?> <?php echo(for_id($option->stem)) ?> unchecked' view='Candidate'>
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
