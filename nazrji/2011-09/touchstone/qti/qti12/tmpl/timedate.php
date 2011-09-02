<?php require("header.php"); ?>	

			<?php echo $headertext ?>
	
<?php foreach ($sets as $respid => $set): ?>
			<response_lid ident="<?php echo $respid ?>">
				<render_choice shuffle="No">
<?php foreach ($set->values as $value): ?>
					<response_label ident="<?php echo $value ?>">
						<material>
							<mattext texttype="text/plain"><?php echo $value ?></mattext>
						</material>
					</response_label>
<?php endforeach; ?>
				</render_choice>
			</response_lid>
<?php endforeach; ?>
		</presentation>

		<resprocessing>
			<outcomes>
				<decvar/>
			</outcomes>

			<respcondition title="Correct" continue="Yes">
				<conditionvar>
<?php foreach ($sets as $respid => $set): ?>
					<varequal respident="<?php echo $respid ?>"><?php echo $set->correct ?></varequal>
<?php endforeach; ?>
				</conditionvar>
				<setvar action="Add">1</setvar>
			</respcondition>
    
			<!-- force general feedback to output -->
			<respcondition title="General Feedback"  continue="Yes">
				<conditionvar>
					<or>
						<other/>
						<not>
							<other/>
						</not>
					</or>
				</conditionvar>
				<setvar action="Add">0</setvar>
				<displayfeedback linkrefid="general"/>
			</respcondition>
      
		</resprocessing>

		<!-- only 1 feedback for timedate questions -->
		<itemfeedback ident="General" view="Candidate">
			<material>
				<mattext texttype='text/html'><![CDATA[<?php echo $question->feedback ?>]]></mattext>
			</material>
		</itemfeedback>
	</item>
