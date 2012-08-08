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
		<qticomment>Display:<?php echo $question->q_option_order ?></qticomment>
		<qticomment>Marking:<?php echo $question->score_method ?></qticomment>
			<?php echo $headertext ?>

            <response_lid ident='1'>
                <render_choice shuffle='No'>
<?php foreach ($question->options as $oid => $option) : ?>
                    <response_label ident='<?php echo $this->ll[$oid] ?>'>
                        <material>
                            <mattext texttype='text/html'><![CDATA[<?php echo $option->stem ?>]]></mattext>
<?php if ($option->media) : ?>
							<matimage imagtype="<?php echo $option->media_type ?>" uri="<?php echo $option->media ?>"/>
<?php endif; ?>
                      </material>
                    </response_label>
<?php endforeach; ?>	
                </render_choice>
            </response_lid>
        </presentation>
		
        <resprocessing>
            <outcomes>
                <decvar/>
            </outcomes>
<?php foreach ($question->options as $oid => $option) : ?>
<?php if ($question->correct != $oid) continue; ?>
            <respcondition title='<?php echo $oid ?> <?php echo(for_id($option->stem)) ?>' >
                <conditionvar>
                    <varequal respident='1'><?php echo $this->ll[$oid] ?></varequal>
                </conditionvar>
                <setvar action='Set'><?php echo $option->marks_correct;?></setvar>
                <displayfeedback linkrefid='correct' />
            </respcondition>
<?php endforeach; ?>	
            <respcondition title='incorrect' >
                <conditionvar>
                    <other/>
                </conditionvar>
                <setvar action='Set'><?php echo $option->marks_incorrect;?></setvar>
                <displayfeedback linkrefid='incorrect'/>
            </respcondition>
        </resprocessing>
		
        <itemfeedback ident='correct' view='Candidate'>
            <material>
                <mattext texttype='text/html'><![CDATA[<?php echo $question->fb_correct ?>]]></mattext>
            </material>
        </itemfeedback>
		
        <itemfeedback ident='incorrect' view='Candidate'>
            <material>
                <mattext texttype='text/html'><![CDATA[<?php echo $question->fb_incorrect ?>]]></mattext>
            </material>
        </itemfeedback>
    </item>
