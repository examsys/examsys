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

<?php foreach ($question->scenarios as $scid => $scenario) : ?>
			<response_lid ident="<?php echo $scid ?>">
				<material>
					<mattext texttype="text/html"><![CDATA[<?php echo $scenario->scenario ?>]]></mattext>
				</material>	
				<render_choice shuffle="No">
<?php foreach ($question->options as $oid => $option) : ?>
					<response_label ident="<?php echo $this->ll[$oid] ?>">
						<material>
							<mattext texttype="text/html"><![CDATA[<?php echo $option->stem ?>]]></mattext>
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
			
<?php foreach ($question->scenarios as $scid => $scenario) : ?>
			<respcondition title="<?php echo $scid ?>" continue="Yes">
				<conditionvar>
					<varequal respident="<?php echo $scid ?>"><?php echo $this->ll[$scenario->answer] ?></varequal>
				</conditionvar>
				<setvar action="Add"><?php echo $option->marks_correct; ?></setvar>
			</respcondition>
      <respcondition title="<?php echo $scid ?>" continue="Yes">
				<conditionvar>
         <not>
					<varequal respident="<?php echo $scid ?>"><?php echo $this->ll[$scenario->answer] ?></varequal>
         </not>
				</conditionvar>
				<setvar action="Add"><?php echo $option->marks_incorrect; ?></setvar>
			</respcondition>
<?php endforeach; ?>
			
		</resprocessing>
	</item>
