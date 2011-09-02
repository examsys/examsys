<?php require("header.php"); ?>	

			<?php echo $headertext ?>

			<response_str ident="1" rcardinality="Single">
				<render_fib>
<?php $respid = 1; ?>
<?php foreach ($question->question as &$q) : ?>
<?php // do we have a blank to output? ?>
<?php if (substr($q,0,1) == "%") :?>
					<response_label ident="rl<?php echo $respid ?>"/>
<?php $respid++; ?>
<?php // otherwise output the material ?>
<?php else: ?>
					<material>
						<mattext texttype="text/html"><![CDATA[<?php echo $q ?>]]></mattext>
					</material>
<?php endif; ?>
<?php endforeach; ?>
				</render_fib>
			</response_str>
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
					<or>
<?php foreach ($optset as $option): ?>
						<varequal respident="1" case="No" index="<?php echo $respid ?>"><?php echo $option->display ?></varequal>
<?php endforeach; ?>
					</or>
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
	