<?= "<?xml version='1.0' encoding='UTF-8' standalone='yes'?>\r\n" ?>
<assessmentItem timeDependent="false" adaptive="false" label="mylabel" title="<?=$title?>" identifier="myidentifier" xsi:schemaLocation="http://www.imsglobal.org/xsd/imsqti_v2p1 imsqti_v2p1.xsd" xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" xmlns:xi="http://www.w3.org/2001/XInclude" xmlns:lip="http://www.imsglobal.org/xsd/imslip_v1p0" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:m="http://www.w3.org/1998/Math/MathML" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
	<responseDeclaration identifier="RESPONSE" cardinality="multiple" baseType="identifier">
		<correctResponse>
<? foreach ($question->options as $oid => $option) : ?>
<? if (!$option->is_correct) continue; ?>
			<value><?= $this->ll[$oid] ?></value>	
<? endforeach; ?>
		</correctResponse>
	</responseDeclaration>
	
	<outcomeDeclaration identifier="SCORE" cardinality="single" baseType="float"/>
<? foreach ($question->options as $oid => $option) : ?>
	<outcomeDeclaration baseType="identifier" cardinality="single" identifier="FEEDBACK<?=$this->ll[$oid]?>"/>
	<outcomeDeclaration baseType="identifier" cardinality="multiple" identifier="ANSWER<?=$this->ll[$oid]?>">
		<defaultValue>
			<value><?= $this->ll[$oid] ?></value>
		</defaultValue>
	</outcomeDeclaration>
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
		<choiceInteraction responseIdentifier="RESPONSE" shuffle="true" maxChoices="0">
            <prompt> 
<? foreach ($question->options as $oid => $option) : ?>
<? if ($option->fb_correct) : ?>
				<feedbackInline identifier="<?=$option->is_correct?'T':'F'?>" outcomeIdentifier="FEEDBACK<?=$this->ll[$oid]?>" showHide="show"><?= $option->fb_correct ?></feedbackInline>
<? endif; ?>
<? if ($option->fb_incorrect) : ?>
				<feedbackInline identifier="<?=$option->is_correct?'F':'T'?>" outcomeIdentifier="FEEDBACK<?=$this->ll[$oid]?>" showHide="show"><?= $option->fb_incorrect ?></feedbackInline>
<? endif; ?>
<? endforeach; ?>
			</prompt>
<? foreach ($question->options as $oid => $option) : ?>
			<simpleChoice identifier="<?=$this->ll[$oid]?>" fixed="false">
				<?= $option->stem ?>
<? if ($option->media) : ?>
				<object type="<?=$option->media_type?>" data="<?=$option->media?>"/>
<? endif; ?>
			</simpleChoice>
<? endforeach; ?>
		</choiceInteraction>
	</itemBody>
	<responseProcessing>
		<responseCondition>
			<responseIf>
				<match>
					<variable identifier="RESPONSE"/>
					<correct identifier="RESPONSE"/>
				</match>
				<setOutcomeValue identifier="SCORE">
					<baseValue baseType="float">1</baseValue>
				</setOutcomeValue>
			</responseIf>
			<responseElse>
				<setOutcomeValue identifier="SCORE">
					<baseValue baseType="float">0</baseValue>
				</setOutcomeValue>
			</responseElse>
		</responseCondition>

		
<? foreach ($question->options as $oid => $option) : ?>
		<responseCondition>
			<responseIf>
				<contains>
					<variable identifier="RESPONSE"/>
					<variable identifier="ANSWER<?=$this->ll[$oid]?>"/>
				</contains>
				<setOutcomeValue identifier="FEEDBACK<?=$this->ll[$oid]?>">
					<baseValue baseType="identifier">T</baseValue>
				</setOutcomeValue>
			</responseIf>
			<responseElse>
				<setOutcomeValue identifier="FEEDBACK<?=$this->ll[$oid]?>">
					<baseValue baseType="identifier">F</baseValue>
				</setOutcomeValue>
			</responseElse>		
		</responseCondition>
<? endforeach; ?>

	</responseProcessing>
<? if ($question->feedback) : ?>
	<modalFeedback identifier="YES" outcomeIdentifier="GENERALFB" showHide="show"><?= $question->feedback ?></modalFeedback>
<? endif; ?>
</assessmentItem>
