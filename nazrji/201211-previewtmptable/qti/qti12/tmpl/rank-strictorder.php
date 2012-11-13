<?php
// This file is part of Rogō
//
// Rogō is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogō is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogō.  If not, see <http://www.gnu.org/licenses/>.
require("header.php");
?>	

			<?php echo $headertext ?>

<?php foreach ($question->options as $oid => $option) : ?>
			<response_lid ident="<?php echo $oid ?>">
				<material>
					<mattext texttype="text/html"><![CDATA[<?php echo $option->stem ?>]]></mattext>
				</material>
				<render_choice shuffle="No">
<?php foreach ($question->optlist as $oid => $option) : ?>
<?php if ($oid == 0) : ?>
					<response_label ident="BLANK">
<?php elseif ($oid == 9990) : ?>
					<response_label ident="NA">
<?php else : ?>
					<response_label ident="<?php echo $this->ll[$oid] ?>">
<?php endif; ?>
						<material>
							<mattext texttype="text/plain"><?php echo $option ?></mattext>
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
<?php foreach ($question->options as $oid => $option) : ?>
<?php if ($option->order < 1 || $option->order > 26) continue; ?>
			<respcondition title="<?php echo $oid ?> <?php echo for_id($option->stem) ?>" continue="Yes">
				<conditionvar>
					<varequal respident="<?php echo $oid ?>" case="No"><?php echo($question->optlist[$option->order]) ?></varequal>
				</conditionvar>
				<setvar action="Add"><?php echo $option->marks_correct; ?></setvar>
			</respcondition>
<?php endforeach; ?>	

			<respcondition title="right" continue="Yes" >
				<conditionvar>
<?php foreach ($question->options as $oid => $option) : ?>
<?php if ($option->order < 1 || $option->order > 26) continue; ?>
					<varequal respident="<?php echo $oid ?>" case="No"><?php echo($question->optlist[$option->order]) ?></varequal>
<?php endforeach; ?>	
				</conditionvar>
				<setvar action="Add"><?php echo $option->marks_correct; ?></setvar>
				<displayfeedback linkrefid="right"/>
			</respcondition>

			<respcondition title="wrong" continue="Yes">
				<conditionvar>
					<or>
<?php foreach ($question->options as $oid => $option) : ?>
<?php if ($option->order < 1 || $option->order > 26) continue; ?>
						<not>
							<varequal respident="<?php echo $oid ?>" case="No"><?php echo($question->optlist[$option->order]) ?></varequal>
						</not>
<?php endforeach; ?>	
					</or>
				</conditionvar>
				<setvar action="Add"><?php echo $option->marks_incorrect; ?></setvar>
				<displayfeedback linkrefid="wrong"/>
			</respcondition>

		</resprocessing>
	
		<itemfeedback ident="right" view="Candidate">
            <material>
                <mattext texttype='text/html'><![CDATA[<?php echo $question->fb_correct ?>]]></mattext>
            </material>
		</itemfeedback>
		<itemfeedback ident="wrong" view="Candidate">
            <material>
                <mattext texttype='text/html'><![CDATA[<?php echo $question->fb_incorrect ?>]]></mattext>
            </material>
		</itemfeedback>
	</item>


