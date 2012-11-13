<?= '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' ?>
<assessmentItem timeDependent="false" adaptive="false" label="mylabel" title="<?=$title?>" identifier="myidentifier" xsi:schemaLocation="http://www.imsglobal.org/xsd/imsqti_v2p1 imsqti_v2p1.xsd" xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" xmlns:xi="http://www.w3.org/2001/XInclude" xmlns:lip="http://www.imsglobal.org/xsd/imslip_v1p0" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:m="http://www.w3.org/1998/Math/MathML" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">

<? foreach ($question->options as $oid => $option) : ?>
	<responseDeclaration baseType="identifier" cardinality="single" identifier="RESPONSE<?=$this->ll[$oid]?>">
        <correctResponse>
            <value><?= $option->iscorrect ? 'T' : 'F' ?></value>
        </correctResponse>
 		<mapping defaultValue="0">
			<mapEntry mapKey="<?=$option->iscorrect?'T':'F'?>" mappedValue="1"/>
			<mapEntry mapKey="<?=$option->iscorrect?'F':'T'?>" mappedValue="<?=$negmark?>"/>
		</mapping>  
	</responseDeclaration>
<? endforeach; ?>		

    <outcomeDeclaration baseType="float" cardinality="single" identifier="SCORE"/>
<? foreach ($question->options as $oid => $option) : ?>
	<outcomeDeclaration baseType="float" cardinality="single" identifier="SCORE<?=$this->ll[$oid]?>"/>
	<outcomeDeclaration baseType="identifier" cardinality="single" identifier="FEEDBACK<?=$this->ll[$oid]?>"/>
<? endforeach; ?>
		
<? if ($question->feedback) : ?>
	<outcomeDeclaration baseType="identifier" cardinality="single" identifier="GENERALFB">
		<defaultValue>
			<value>YES</value>
		</defaultValue>
	</outcomeDeclaration>
<? endif; ?>
	
    <itemBody>
		
		<?= $headertext ?>
<? if ($question->media) : ?>
		<p><object type="<?=$question->media_type?>" data="<?=$question->media?>"/></p>
<? endif; ?>
<? foreach ($question->options as $oid => $option) : ?>
 		<p><?= $option->text ?></p>
<? if ($option->media) : ?>
		<p><object type="<?=$option->media_type?>" data="<?=$option->media?>"/></p>
<? endif; ?>
       <choiceInteraction maxChoices="1" responseIdentifier="RESPONSE<?=$this->ll[$oid]?>" shuffle="true">
            <prompt> 
<? if ($option->fb_correct) : ?>
				<feedbackInline identifier="<?=$option->iscorrect?'T':'F'?>" outcomeIdentifier="FEEDBACK<?=$this->ll[$oid]?>" showHide="show"><?= $option->fb_correct ?></feedbackInline>
<? endif; ?>
<? if ($option->fb_incorrect) : ?>
				<feedbackInline identifier="<?=$option->iscorrect?'F':'T'?>" outcomeIdentifier="FEEDBACK<?=$this->ll[$oid]?>" showHide="show"><?= $option->fb_incorrect ?></feedbackInline>
<? if ($hasab) : ?>
				<feedbackInline identifier="A" outcomeIdentifier="FEEDBACK<?=$this->ll[$oid]?>" showHide="show"><?= $option->fb_incorrect ?></feedbackInline>
<? endif; ?>
<? endif; ?>
			</prompt>
            <simpleChoice fixed="true" identifier="T"><?= $true ?></simpleChoice>
            <simpleChoice fixed="true" identifier="F"><?= $false ?></simpleChoice>
<? if ($hasab) : ?>
			<simpleChoice fixed="true" identifier="A">Abstain</simpleChoice>
<? endif; ?>
		</choiceInteraction>
<? endforeach; ?>		

	</itemBody>
		
    <responseProcessing>
		
        <responseCondition>
            <responseIf>
                <isNull>
                    <variable identifier="SCORE"/>
                </isNull>
                <setOutcomeValue identifier="SCORE">
                    <baseValue baseType="float">0</baseValue>
                </setOutcomeValue>
            </responseIf>
        </responseCondition>
			
<? foreach ($question->options as $oid => $option) : ?>
		<responseCondition>
			<responseIf>
				<isNull>
					<variable identifier="RESPONSE<?=$this->ll[$oid]?>"/>
				</isNull>
				<setOutcomeValue identifier="SCORE<?=$this->ll[$oid]?>">
					<baseValue baseType="float">0</baseValue>
				</setOutcomeValue>
			</responseIf>
			<responseElse>
				<setOutcomeValue identifier="SCORE<?=$this->ll[$oid]?>">
					<mapResponse identifier="RESPONSE<?=$this->ll[$oid]?>"/>
				</setOutcomeValue>
			</responseElse>
		</responseCondition>
<? endforeach; ?>		
	
	
        <setOutcomeValue identifier="SCORE">
            <sum>
<? foreach ($question->options as $oid => $option) : ?>
                <variable identifier="SCORE<?=$this->ll[$oid]?>"/>
<? endforeach; ?>       
            </sum>
        </setOutcomeValue>

<? foreach ($question->options as $oid => $option) : ?>
		<setOutcomeValue identifier="FEEDBACK<?=$this->ll[$oid]?>">
            <variable identifier="RESPONSE<?=$this->ll[$oid]?>"/>
        </setOutcomeValue>
<? endforeach; ?>		
			
    </responseProcessing>

<? if ($question->feedback) : ?>
	<modalFeedback identifier="YES" outcomeIdentifier="GENERALFB" showHide="show"><?= $question->feedback ?></modalFeedback>
<? endif; ?>
</assessmentItem>
