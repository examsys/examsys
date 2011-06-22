<?php require("header.php"); ?>
			<qticomment><![CDATA[RAW_LABELLING:<?php echo $question->raw_option ?>]]></qticomment>

			<?php echo $headertext ?>

			<response_xy ident="IMAGE" rcardinality="Multiple">
				<render_hotspot>
					<material>
						<matimage imagtype="image/gif" uri="<?php echo $question->media ?>" width="<?php echo $question->media_width ?>" height="<?php echo $question->media_height ?>"/>
					</material>
<?php foreach ($question->labels as $id => $label): ?>					
					<response_label ident="<?php echo $this->ll[$id+1] ?>">
						<material>
							<mattext><?php echo $label->tag; ?></mattext>
						</material>
					</response_label>
<?php endforeach; ?>
				</render_hotspot>
			</response_xy>
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

<?php foreach ($question->labels as $id => $label): ?>	
<?php if ($label->left == -1 && $label->top == -1) continue; ?>				
			<respcondition title="<?php echo $this->ll[$id+1] ?>" continue="Yes">
				<conditionvar>
					<varinside respident="<?php echo $this->ll[$id+1] ?>" areatype="Rectangle"><?php echo $label->left ?>,<?php echo $label->top ?> <?php echo $label->left+$question->width ?>,<?php echo($label->top+$question->height) ?></varinside>
				</conditionvar>
				<setvar action="Add">1</setvar>
			</respcondition>
<?php endforeach; ?>
		</resprocessing>

		<!-- only 1 feedback for timedate questions -->
		<itemfeedback ident="General" view="Candidate">
			<material>
				<mattext texttype='text/html'><![CDATA[<?php echo $question->feedback ?>]]></mattext>
			</material>
		</itemfeedback>
	</item>
