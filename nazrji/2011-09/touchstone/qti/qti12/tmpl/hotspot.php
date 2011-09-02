<?php require("header.php"); ?>
			<qticomment><![CDATA[RAW_HOTSPOT:<?php echo $question->raw_option ?>]]></qticomment>

			<?php echo $headertext ?>
			<response_xy ident="IMAGE" rcardinality="Single" rtiming="No">
				<render_hotspot> 
					<material>
						<matimage imagtype="image/gif" uri="<?php echo $question->media ?>" x0="0" width="<?php echo $question->media_width ?>" y0="0" height="<?php echo $question->media_height ?>"/>
					</material>
<?php $hid = 1;?>
<?php foreach ($question->hotspots as $hotspot):?>
<?php if ($hotspot->type == "rectangle"): ?>
					<response_label ident="<?php echo $this->ll[$hid] ?>" rarea="Rectangle"><?php echo implode(",",$hotspot->coords) ?></response_label>
<?php elseif ($hotspot->type == "ellipse"): ?>
<response_label ident="<?php echo $this->ll[$hid] ?>" rarea="Ellipse"><?php echo(implode(",",$hotspot->coords)) ?></response_label>
<?php else: ?>
					<qticomment>Hotspot:<?php echo $hotspot->type ?>|<?php echo(implode(",",$hotspot->coords)) ?></qticomment>
<?php endif; ?>
<?php $hid++; ?>
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

<?php $hid = 1;?>
<?php foreach ($question->hotspots as $hotspot):?>
<?php if ($hotspot->type != "rectangle" && $hotspot->type != "ellipse") continue; ?>
			<respcondition>
				<conditionvar>
<?php if ($hotspot->type == "rectangle"): ?>
					<varinside respident="IMAGE" areatype="Rectangle"><?php echo(implode(",",$hotspot->coords)) ?></varinside>
<?php elseif ($hotspot->type == "ellipse"): ?>
					<varinside respident="IMAGE" areatype="Ellipse"><?php echo(implode(",",$hotspot->coords)) ?></varinside>
<?php endif; ?>
				</conditionvar>
				<setvar action="Set">1</setvar>
				<displayfeedback feedbacktype = "Response" linkrefid = "Correct"/>
			</respcondition>
<?php $hid++; ?>
<?php endforeach; ?>
		</resprocessing>

		<!-- only 1 feedback for timedate questions -->
		<itemfeedback ident="General" view="Candidate">
			<material>
				<mattext texttype='text/html'><![CDATA[<?php echo $question->feedback ?>]]></mattext>
			</material>
		</itemfeedback>
	</item>
