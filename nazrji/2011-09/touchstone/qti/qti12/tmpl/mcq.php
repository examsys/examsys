<?php require("header.php"); ?>	
		<qticomment>Display:<?php echo $question->presentation ?></qticomment>
		
			<?php echo $headertext ?>

            <response_lid ident='1'>
                <render_choice shuffle='No'>
<?php foreach ($question->options as $oid => $option): ?>
                    <response_label ident='<?php echo $this->ll[$oid] ?>'>
                        <material>
                            <mattext texttype='text/html'><![CDATA[<?php echo $option->stem ?>]]></mattext>
<?php if ($option->media): ?>
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
<?php foreach ($question->options as $oid => $option): ?>
<?php if ($question->correct != $oid) continue; ?>
            <respcondition title='<?php echo $oid ?> <?php echo(for_id($option->stem)) ?>' >
                <conditionvar>
                    <varequal respident='1'><?php echo $this->ll[$oid] ?></varequal>
                </conditionvar>
                <setvar action='Set'>1</setvar>
                <displayfeedback linkrefid='correct' />
            </respcondition>
<?php endforeach; ?>	
            <respcondition title='incorrect' >
                <conditionvar>
                    <other/>
                </conditionvar>
                <setvar action='Set'>0</setvar>
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
