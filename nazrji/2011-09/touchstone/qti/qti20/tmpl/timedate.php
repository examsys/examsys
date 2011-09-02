<?='<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'?>
<assessmentItem timeDependent="false" adaptive="false" label="mylabel" title="<?=$title?>" identifier="myidentifier" xsi:schemaLocation="http://www.imsglobal.org/xsd/imsqti_v2p1 imsqti_v2p1.xsd" xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" xmlns:xi="http://www.w3.org/2001/XInclude" xmlns:lip="http://www.imsglobal.org/xsd/imslip_v1p0" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:m="http://www.w3.org/1998/Math/MathML" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">

<? foreach ($sets as $respid => $set): ?>
	<responseDeclaration identifier="RESPONSE<?=$respid?>" cardinality="single" baseType="identifier">
		<correctResponse>
			<value><?=$this->ll[$set->correct]?></value>
		</correctResponse>
		<mapping defaultValue="0">
			<mapEntry mapKey="<?=$this->ll[$respid]?><?=$set->correct?>" mappedValue="1"/>
		</mapping>
	</responseDeclaration>
<? endforeach; ?>

	<outcomeDeclaration identifier="SCORE" cardinality="single" baseType="float"/>
<? if ($question->feedback): ?>
	<outcomeDeclaration baseType="identifier" cardinality="single" identifier="GENERALFB">
		<defaultValue>
			<value>YES</value>
		</defaultValue>
	</outcomeDeclaration>
<? endif; ?>

	<itemBody>
		<?=$headertext?>
<? if ($question->media): ?>
		<p><object type="<?=$question->media_type?>" data="<?=$question->media?>"/></p>
<? endif; ?>
		<p>
<? foreach ($sets as $respid => $set): ?>
			<inlineChoiceInteraction responseIdentifier="RESPONSE<?=$respid?>" shuffle="false">
<? foreach ($set->values as $vid => $value): ?>
				<inlineChoice identifier="<?=$this->ll[$respid]?><?=$vid?>"><?=$value?></inlineChoice>
<? endforeach; ?>
			</inlineChoiceInteraction>
<? endforeach; ?>	
		</p>
	</itemBody>

	<responseProcessing>
	
	    <responseCondition>
			<responseIf>
				<and>
<? foreach ($sets as $respid => $set): ?>
					<match>
						<variable identifier="RESPONSE<?=$respid?>"/>
						<correct identifier="RESPONSE<?=$respid?>"/>
					</match>
<? endforeach; ?>	
				</and>
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
		
	</responseProcessing>

<? if ($question->feedback): ?>
	<modalFeedback identifier="YES" outcomeIdentifier="GENERALFB" showHide="show"><?=$question->feedback?></modalFeedback>
<? endif; ?>
</assessmentItem>
