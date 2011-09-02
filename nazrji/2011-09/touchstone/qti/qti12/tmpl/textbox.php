<?php require("header.php"); ?>	
		<qticomment>Editor:<?php echo $question->editor ?></qticomment>

			<?php echo $headertext ?>

			<response_str ident="1" rcardinality="Single">
				<render_fib fibtype="String" prompt="Box" rows="<?php echo $question->rows ?>" columns="<?php echo $question->columns ?>" />
			</response_str>
		</presentation>
		<resprocessing>
			<outcomes>
				<decvar/>
			</outcomes>
			<!-- no actual scoring takes place automatically -->
			<respcondition title="Unscored" >
				<conditionvar>
					<or>
						<other/>
						<not>
							<other/>
						</not>
					</or>
				</conditionvar>
				<setvar action="Add">0</setvar>
				<displayfeedback linkrefid="Unscored"/>
			</respcondition>
			<respcondition title="Scored" >
				<conditionvar>
					<other/>
				</conditionvar>
				<setvar action="Set"><?php echo $question->marks ?></setvar>
				<displayfeedback linkrefid="Scored"/>
			</respcondition>
		</resprocessing>
		
		<itemfeedback ident="Unscored" view="Candidate">
			<material>
				<mattext texttype="text/html"><![CDATA[<?php echo $question->feedback ?>]]></mattext>
			</material>
		</itemfeedback>
		
		<itemfeedback ident="Scored" view="Candidate">
		</itemfeedback>
	</item>

