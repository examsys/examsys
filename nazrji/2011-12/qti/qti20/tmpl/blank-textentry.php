<?= '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' ?>
<assessmentItem timeDependent="false" adaptive="false" label="mylabel" title="<?=$title?>" identifier="myidentifier" xsi:schemaLocation="http://www.imsglobal.org/xsd/imsqti_v2p1 imsqti_v2p1.xsd" xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" xmlns:xi="http://www.w3.org/2001/XInclude" xmlns:lip="http://www.imsglobal.org/xsd/imslip_v1p0" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:m="http://www.w3.org/1998/Math/MathML" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">


<? $respid = 1; ?>
<? foreach ($question->options as & $optset) : ?>
	<responseDeclaration identifier="RESPONSE<?=$respid?>" cardinality="single" baseType="string">
		<correctResponse>
			<value><?= reset($optset)->display ?></value>
		</correctResponse>
		<mapping defaultValue="0">
<? foreach ($optset as $option) : ?>
			<mapEntry mapKey="<?=$option->display?>" mappedValue="1"/>
<? endforeach; ?>
		</mapping>
<? $respid++; ?>
	</responseDeclaration>
<? endforeach; ?>



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
<? $respid = 1; ?>
<? foreach ($question->question as & $q) : ?>
<? // do we have a blank to output? ?>
<? if (substr($q, 0, 1) == "%") : ?>
		<textEntryInteraction responseIdentifier="RESPONSE<?=$respid?>" expectedLength="15"/>
<? $respid++; ?>
<? // otherwise output the material ?>
<? else : ?>
			<?= $q ?>
<? endif; ?>
<? endforeach; ?>
			
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

<? $respid = 1; ?>
<? foreach ($question->options as & $optset) : ?>
	    <responseCondition>
			<responseIf>
				<match>
					<variable identifier="RESPONSE<?=$respid?>"/>
					<correct identifier="RESPONSE<?=$respid?>"/>
				</match>
				<setOutcomeValue identifier="SCORE">
					<sum>
						<variable identifier="SCORE"/>  
						<baseValue baseType="float">1</baseValue>   
					</sum>
				</setOutcomeValue>
			</responseIf>
		</responseCondition>
<? $respid++; ?>
<? endforeach; ?>
	</responseProcessing>


	
<? if ($question->feedback) : ?>
	<modalFeedback identifier="YES" outcomeIdentifier="GENERALFB" showHide="show"><?= $question->feedback ?></modalFeedback>
<? endif; ?>

</assessmentItem>
