<?= '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' ?>
<assessmentItem timeDependent="false" adaptive="false" label="mylabel" title="<?=$title?>" identifier="myidentifier" xsi:schemaLocation="http://www.imsglobal.org/xsd/imsqti_v2p1 imsqti_v2p1.xsd" xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" xmlns:xi="http://www.w3.org/2001/XInclude" xmlns:lip="http://www.imsglobal.org/xsd/imslip_v1p0" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:m="http://www.w3.org/1998/Math/MathML" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">

	<responseDeclaration identifier="RESPONSE" cardinality="single" baseType="string">
		<correctResponse>
			<value><?= $answer ?></value>
		</correctResponse>
		<mapping defaultValue="0">
			<mapEntry mapKey="<?=$answer?>" mappedValue="1"/>
		</mapping>
	</responseDeclaration>

	<outcomeDeclaration identifier="SCORE" cardinality="single" baseType="float"/>
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
		<p>
			<textEntryInteraction responseIdentifier="RESPONSE" expectedLength="15"/>
		</p>
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

	    <responseCondition>
			<responseIf>
				<match>
					<variable identifier="RESPONSE"/>
					<correct identifier="RESPONSE"/>
				</match>
				<setOutcomeValue identifier="SCORE">
					<sum>
						<variable identifier="SCORE"/>  
						<baseValue baseType="float">1</baseValue>   
					</sum>
				</setOutcomeValue>
			</responseIf>
		</responseCondition>
	</responseProcessing>
	
<? if ($question->feedback) : ?>
	<modalFeedback identifier="YES" outcomeIdentifier="GENERALFB" showHide="show"><?= $question->feedback ?></modalFeedback>
<? endif; ?>

</assessmentItem>
