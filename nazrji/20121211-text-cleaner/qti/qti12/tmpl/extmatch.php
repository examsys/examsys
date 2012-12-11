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

echo $headertext; 
?>			
<?php foreach ($question->scenarios as $respid => $scn) : ?>
<?php $c = '';
  if (count($scn->correctans) > 1) {
    $c = "rcardinality='Multiple'";
  }
?>
			<response_lid ident="<?php echo $respid ?>" <?php echo $c ?>>
				<material>
					<mattext texttype="text/html"><![CDATA[<?php echo $scn->stem ?>]]></mattext>
<?php if ($scn->media) : ?>
					<matimage imagtype="<?php echo $scn->media_type ?>" uri="<?php echo $scn->media ?>"/>
<?php endif; ?>
				</material>
				
				<render_choice shuffle="No">
<?php foreach ($question->optionlist as $oid => $option) : ?>
					<response_label ident="<?php echo $this->ll[$oid] ?>">
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

<?php foreach ($question->scenarios as $respid => $scn) : ?>
<?php foreach ($scn->correctans as $aid => $answer) : ?>
			<respcondition title="Stem <?php echo $respid ?> <?php echo $aid ?>" continue="Yes">
				<conditionvar>
					<varequal respident="<?php echo $respid ?>"><?php echo $this->ll[$answer] ?></varequal>
				</conditionvar>
				<setvar action="Add"><?php echo $scn->marks_correct; ?></setvar>
			</respcondition>
<?php endforeach; ?>
<?php endforeach; ?>

<?php foreach ($question->scenarios as $respid => $scn) : ?>
<?php foreach ($scn->correctans as $aid => $answer) : ?>
			<respcondition title="Stem <?php echo $respid ?> <?php echo $aid ?>" continue="Yes">
				<conditionvar>
         <not>
					<varequal respident="<?php echo $respid ?>"><?php echo $this->ll[$answer] ?></varequal>
				 </not>
        </conditionvar>
				<setvar action="Add"><?php echo $scn->marks_incorrect; ?></setvar>
			</respcondition>
<?php endforeach; ?>
<?php endforeach; ?>

<?php foreach ($question->scenarios as $respid => $scn) : ?>
			<respcondition title="Feedback <?php echo $respid ?>" continue="Yes">
				<conditionvar>
					<or>
						<varequal respident="<?php echo $respid ?>">A</varequal>
						<not>
							<varequal respident="<?php echo $respid ?>">A</varequal>
						</not>
					</or>
				</conditionvar>
				<setvar action="Add">0</setvar>
				<displayfeedback linkrefid="Feedback <?php echo $respid ?>"/>
			</respcondition>
<?php endforeach; ?>
		</resprocessing>
    
<?php foreach ($question->scenarios as $respid => $scn) : ?>
		<itemfeedback ident="Feedback <?php echo $respid ?>" view="Candidate">
			<material>
				<mattext texttype='text/html'><![CDATA[<?php echo $scn->feedback ?>]]></mattext>
			</material>      
		</itemfeedback>
<?php endforeach; ?>
	</item>
