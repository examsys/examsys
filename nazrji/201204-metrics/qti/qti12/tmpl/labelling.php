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
			<qticomment><![CDATA[RAW_LABELLING:<?php echo $question->raw_option ?>]]></qticomment>

			<?php echo $headertext ?>

			<response_xy ident="IMAGE" rcardinality="Multiple">
				<render_hotspot>
					<material>
						<matimage imagtype="image/gif" uri="<?php echo $question->media ?>" width="<?php echo $question->media_width ?>" height="<?php echo $question->media_height ?>"/>
					</material>
<?php foreach ($question->labels as $id => $label) : ?>					
					<response_label ident="<?php echo $this->ll[$id+1] ?>">
						<material>
              <?php if ($label->type == 'text') : ?>
							  <mattext><?php echo $label->tag; ?></mattext>
              <?php else : ?>
                <matimage imagtype="image/gif" uri="<?php echo $label->tag; ?>" width="<?php echo $label->width ?>" height="<?php echo $label->height ?>"/>
              <?php endif; ?>
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

<?php foreach ($question->labels as $id => $label) : ?>	
<?php if ($label->left == - 1 && $label->top == - 1) continue; ?>				
			<respcondition title="<?php echo $this->ll[$id+1] ?>" continue="Yes">
				<conditionvar>
					<varinside respident="<?php echo $this->ll[$id+1] ?>" areatype="Rectangle"><?php echo $label->left ?>,<?php echo $label->top ?> <?php echo $label->left + $question->width ?>,<?php echo($label->top + $question->height) ?></varinside>
				</conditionvar>
				<setvar action="Add"><?php echo $question->marks_correct; ?></setvar>
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
