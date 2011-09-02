<?php require("header.php"); ?>
	
			<?php echo $headertext ?>

			<response_lid ident="1">
				<render_choice shuffle="No">
<?php foreach ($question->scale as $oid => $scale): ?>
					<response_label ident="<?php echo $this->ll[$oid] ?>">
						<material>
							<mattext texttype="text/html"><![CDATA[<?php echo $scale ?>]]></mattext>
						</material>
					</response_label>
<?php endforeach; ?>	
<?php if ($question->hasna): ?>
					<response_label ident="Not Applicable">
						<material>
							<mattext texttype="text/html"><![CDATA[Not Applicable]]></mattext>
						</material>
					</response_label>
<?php endif; ?>
				</render_choice>
			</response_lid>
		</presentation>
		
		<resprocessing>
			<outcomes>
				<decvar/>
			</outcomes>
			
<?php foreach ($question->scale as $oid => $scale): ?>
			<respcondition title="<?php echo $oid ?> <?php echo(for_id($scale)) ?>" >
				<conditionvar>
					<varequal respident="1"><?php echo $this->ll[$oid] ?></varequal>
				</conditionvar>
				<setvar action="Set"><?php echo $oid ?></setvar>
			</respcondition>
<?php endforeach; ?>	
			
		</resprocessing>
	</item>

