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
				<setvar action="Add"><?php echo $question->marks_incorrect; ?></setvar>
				<displayfeedback linkrefid="Unscored"/>
			</respcondition>
			<respcondition title="Scored" >
				<conditionvar>
					<other/>
				</conditionvar>
				<setvar action="Set"><?php echo $question->marks_correct; ?></setvar>
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

