<?= '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' ?>
<assessmentItem timeDependent="false" adaptive="false" label="mylabel" title="<?=$title?>" identifier="myidentifier" xsi:schemaLocation="http://www.imsglobal.org/xsd/imsqti_v2p1 imsqti_v2p1.xsd" xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" xmlns:xi="http://www.w3.org/2001/XInclude" xmlns:lip="http://www.imsglobal.org/xsd/imslip_v1p0" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:m="http://www.w3.org/1998/Math/MathML" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">


<? foreach ($question->options as $oid => & $o) : ?>
<? if ($o->order == 0) : ?>
<? $label = ""; ?>
<? elseif ($o->order == 9990) : ?>
<? $label = ""; ?>
<? else : ?>
<? $label = $this->ll[$o->order]; ?>
<? endif; ?>
<? if ($label != "") : ?>
	<responseDeclaration identifier="RESPONSE<?=$oid?>" cardinality="single" baseType="identifier">
	<correctResponse>
			<value><?= $label ?></value>
		</correctResponse>
		<mapping defaultValue="0">
			<mapEntry mapKey="<?=$label?>" mappedValue="1"/>
		</mapping>
	</responseDeclaration>
<? else : ?>
	<responseDeclaration identifier="RESPONSE<?=$oid?>" cardinality="single" baseType="identifier" />
<? endif; ?>
<? endforeach; ?>

	<outcomeDeclaration identifier="SCORE" cardinality="single" baseType="float"/>
    <outcomeDeclaration baseType="identifier" cardinality="single" identifier="FEEDBACK">
		<defaultValue>
			<value>YES</value>
		</defaultValue>
	</outcomeDeclaration>
    <outcomeDeclaration baseType="identifier" cardinality="single" identifier="FEEDBACK_X">
		<defaultValue>
			<value>NO</value>
		</defaultValue>
	</outcomeDeclaration>

	<itemBody>
		<?= $headertext ?>	
<? if ($question->media) : ?>
		<p><object type="<?=$question->media_type?>" data="<?=$question->media?>"/></p>
<? endif; ?>
<? $respid = 1; ?>
<? foreach ($question->options as $oid => & $o) : ?>
		<p><?= $o->stem ?></p>
		<p>
			<inlineChoiceInteraction responseIdentifier="RESPONSE<?=$oid?>" shuffle="false">
<? foreach ($question->optlist as $oid => $option) : ?>
<? if ($oid == 0) : ?>
<? $label = "BLANK"; ?>
<? elseif ($oid == 9990) : ?>
<? $label = "NA"; ?>
<? else : ?>
<? $label = $this->ll[$oid]; ?>
<? endif; ?>
				<inlineChoice identifier="<?=$label?>"><?= $option ?></inlineChoice>
<? endforeach; ?>
			</inlineChoiceInteraction>
		</p>
<? endforeach; ?>
	</itemBody>


	
	<responseProcessing>
	    <responseCondition>
			<responseIf>
				<and>
<? foreach ($question->options as $oid => & $o) : ?>
<? if ($o->order == 0) : ?>
<? $label = ""; ?>
<? elseif ($o->order == 9990) : ?>
<? $label = ""; ?>
<? else : ?>
<? $label = $this->ll[$o->order]; ?>
<? endif; ?>
<? if ($label != "") : ?>
					<match>
						<variable identifier="RESPONSE<?=$oid?>"/>
						<correct identifier="RESPONSE<?=$oid?>"/>
					</match>
<? endif; ?>
<? endforeach; ?>
				</and>
				<setOutcomeValue identifier="SCORE">
					<baseValue baseType="float">1</baseValue>   
				</setOutcomeValue>
				<setOutcomeValue identifier="FEEDBACK">
				  <variable identifier="FEEDBACK_X"/>
				</setOutcomeValue>
			</responseIf>
		</responseCondition>
    
	</responseProcessing>


	
  <modalFeedback outcomeIdentifier="FEEDBACK" identifier="YES" showHide="hide"><?= $question->fb_correct ?></modalFeedback>
  <modalFeedback outcomeIdentifier="FEEDBACK" identifier="YES" showHide="show"><?= $question->fb_incorrect ?></modalFeedback>

</assessmentItem>
