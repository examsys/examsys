<?php require("header.php"); ?>	

			<?php echo $headertext ?>
			
<?php $respid = 1; ?>
<?php foreach ($question->question as &$q) : ?>
<?php // do we have a blank to output? ?>
<?php if (substr($q,0,1) == "%") :?>
			<response_lid ident="<?php echo $respid++ ?>">
				<render_choice shuffle="Yes">
<?php // get the options set for this blank ?>
<?php $optset = $question->options[$q]; ?>
<?php // output each option in the set ?>
<?php $oid = 1; ?>
<?php foreach ($optset as $option): ?>
					<response_label ident="<?php echo(for_id($option->display)) ?>">
						<material>
							<mattext texttype="text/plain"><?php echo $option->display ?></mattext>
						</material>
					</response_label>
<?php $oid++; ?>
<?php endforeach; ?>
				</render_choice>
			</response_lid>
<?php // otherwise output the material ?>
<?php else: ?>
			<material>
				<mattext texttype="text/html"><![CDATA[<?php echo $q ?>]]></mattext>
			</material>
<?php endif; ?>
<?php endforeach; ?>
		</presentation>
		
		<resprocessing>
			<outcomes>
				<decvar/>
			</outcomes>
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

<?php $respid = 1; ?>
<?php foreach ($question->options as &$optset) : ?>
			<respcondition title="right - <?php echo $respid ?>" continue="Yes">
				<conditionvar>
<?php $oid = 1; ?>
<?php foreach ($optset as $option): ?>
<?php if ($option->correct): ?>
					<varequal respident="<?php echo $respid ?>" case="No"><?php echo $option->display ?></varequal>
<?php endif; ?>
<?php $oid++; ?>
<?php endforeach; ?>
				</conditionvar>
				<setvar action="Add">1</setvar>
			</respcondition>
<?php $respid++; ?>
<?php endforeach; ?>
		</resprocessing>
		
		<!-- only 1 feedback for dropdown questions -->
		<itemfeedback ident="General" view="Candidate">
			<material>
				<mattext texttype='text/html'><![CDATA[<?php echo $question->feedback ?>]]></mattext>
			</material>
		</itemfeedback>
	</item>
	