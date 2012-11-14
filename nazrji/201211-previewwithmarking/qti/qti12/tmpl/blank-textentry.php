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

			<response_str ident="1" rcardinality="Single">
				<render_fib>
<?php $respid = 1; ?>
<?php foreach ($question->question as & $q) : ?>
<?php // do we have a blank to output? ?>
<?php if (substr($q, 0, 1) == "%") : ?>
					<response_label ident="rl<?php echo $respid ?>"/>
<?php $respid++; ?>
<?php // otherwise output the material ?>
<?php else : ?>
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
<?php foreach ($question->options as & $optset) : ?>
			<respcondition title="right - <?php echo $respid ?>" continue="Yes">
				<conditionvar>
					<or>
<?php foreach ($optset as $option) : ?>
						<varequal respident="1" case="No" index="<?php echo $respid ?>"><?php echo $option->display ?></varequal>
<?php endforeach; ?>
					</or>
				</conditionvar>
				  <setvar action="Add"><?php echo $option->marks_correct; ?></setvar>
			</respcondition>
<?php $respid++; ?>
<?php endforeach; ?>

<?php $respid = 1; ?>
<?php foreach ($question->options as & $optset) : ?>
			<respcondition title="right - <?php echo $respid ?>" continue="Yes">
				<conditionvar>
					<or>
<?php foreach ($optset as $option) : ?>
           <not>
						<varequal respident="1" case="No" index="<?php echo $respid ?>"><?php echo $option->display ?></varequal>
           </not>
<?php endforeach; ?>
					</or>
				</conditionvar>
				  <setvar action="Add"><?php echo $option->marks_incorrect; ?></setvar>
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
	