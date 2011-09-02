<?php require("header.php"); ?>
			<qticomment><![CDATA[Question:<?php echo $question->origleadin ?>]]></qticomment>
			<qticomment><![CDATA[Formula:<?php echo $question->formula ?>]]></qticomment>
			<qticomment><![CDATA[Units:<?php echo $question->units ?>]]></qticomment>
			<qticomment><![CDATA[Decimals:<?php echo $question->decimals ?>]]></qticomment>
			<qticomment><![CDATA[Tolerance:<?php echo $question->tolerance ?>]]></qticomment>
<?php foreach ($question->variables as $id => $var): ?>
			<qticomment><![CDATA[Variable:<?php echo $id ?>|<?php echo $var->min ?>|<?php echo $var->max ?>|<?php echo $var->dec ?>|<?php echo $var->inc ?>]]></qticomment>
<?php endforeach; ?>

			<?php echo $headertext ?>
			
			<response_num ident="1" rcardinality="Single">
				<render_fib fibtype="Decimal" prompt="Box" rows="1" columns="10" maxchars="10"/>
			</response_num>
		</presentation>
		
		<resprocessing>
			<outcomes>
				<decvar/>
			</outcomes>
			<respcondition title="right">
				<conditionvar>
					<varequal respident="1"><?php echo $answer ?></varequal>
				</conditionvar>
				<setvar action="Set">1</setvar>
				<displayfeedback linkrefid="general"/>
			</respcondition>
			
<?php if ($question->tolerance > 0) : ?>
			<respcondition title="Within range" >
				<conditionvar>
					<vargte respident="1"><?php echo $answer-$question->tolerance ?></vargte>
					<varlte respident="1"><?php echo $answer+$question->tolerance ?></varlte>
				</conditionvar>
				<setvar action="Set">1</setvar>
				<displayfeedback linkrefid="general"/>
			</respcondition>
<?php endif; ?>

			<!-- force general feedback to output -->
			<respcondition title="General Feedback">
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
		
		<itemfeedback ident="general" view="Candidate">
			<material>
				<mattext texttype="text/html"><![CDATA[<?php echo $question->feedback ?>]]></mattext>
			</material>
		</itemfeedback>
	</item>
