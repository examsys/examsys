<?php require("header.php"); ?>	
		
			<?php echo $headertext ?>

<?php foreach($question->scenarios as $scid => $scenario): ?>
			<response_lid ident="<?php echo $scid ?>">
				<material>
					<mattext texttype="text/html"><![CDATA[<?php echo $scenario->scenario ?>]]></mattext>
				</material>	
				<render_choice shuffle="No">
<?php foreach ($question->options as $oid => $option): ?>
					<response_label ident="<?php echo $this->ll[$oid] ?>">
						<material>
							<mattext texttype="text/html"><![CDATA[<?php echo $option ?>]]></mattext>
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
			
<?php foreach($question->scenarios as $scid => $scenario): ?>
			<respcondition title="<?php echo $scid ?>" continue="Yes">
				<conditionvar>
					<varequal respident="<?php echo $scid ?>"><?php echo $this->ll[$scenario->answer] ?></varequal>
				</conditionvar>
				<setvar action="Add">1</setvar>
			</respcondition>
<?php endforeach; ?>
			
		</resprocessing>
	</item>
