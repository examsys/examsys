<?= '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' ?>
<assessmentItem timeDependent="false" adaptive="false" label="mylabel" title="<?=$title?>" identifier="myidentifier" xsi:schemaLocation="http://www.imsglobal.org/xsd/imsqti_v2p1 imsqti_v2p1.xsd" xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" xmlns:xi="http://www.w3.org/2001/XInclude" xmlns:lip="http://www.imsglobal.org/xsd/imslip_v1p0" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:m="http://www.w3.org/1998/Math/MathML" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">

<? foreach ($question->scenarios as $sid => $scenario) : ?>
	<responseDeclaration baseType="identifier" cardinality="<?=count($scenario->correctans)==1?'single':'multiple'?>" identifier="RESPONSE<?=$this->ll[$sid]?>">
        <correctResponse>
<? foreach ($scenario->correctans as $correct) : ?>
            <value><?= $this->ll[$correct] ?></value>
<? endforeach; ?>
        </correctResponse>
	</responseDeclaration>
<? endforeach; ?>

    <outcomeDeclaration baseType="float" cardinality="single" identifier="SCORE"/>

<? foreach ($question->optionlist as $oid => $option) : ?>
	<outcomeDeclaration baseType="identifier" cardinality="single" identifier="ANSWERS<?=$this->ll[$oid]?>">
		<defaultValue>
			<value><?= $this->ll[$oid] ?></value>
		</defaultValue>
	</outcomeDeclaration>
	<outcomeDeclaration baseType="identifier" cardinality="multiple" identifier="ANSWERM<?=$this->ll[$oid]?>">
		<defaultValue>
			<value><?= $this->ll[$oid] ?></value>
		</defaultValue>
	</outcomeDeclaration>
<? endforeach; ?>

	<outcomeDeclaration baseType="identifier" cardinality="single" identifier="GENERALFB" >
		<defaultValue>
			<value>YES</value>
		</defaultValue>
	</outcomeDeclaration>
	<outcomeDeclaration baseType="identifier" cardinality="single" identifier="SHOWGENERALFB" />
	
    <itemBody>
		
		<?= $headertext ?>	
<? if ($question->media) : ?>
		<p><object type="<?=$question->media_type?>" data="<?=$question->media?>"/></p>
<? endif; ?>
		
<? foreach ($question->scenarios as $sid => $scenario) : ?>
 		<p><?= str_replace("&amp;", "", $scenario->stem) ?></p>
<? if ($scenario->media) : ?>
		<p><object type="<?=$scenario->media_type?>" data="<?=$scenario->media?>"/></p>
<? endif; ?>
        <choiceInteraction maxChoices="<?=count($scenario->correctans)?>" responseIdentifier="RESPONSE<?=$this->ll[$sid]?>" shuffle="true">
<? if ($scenario->feedback) : ?>
            <prompt> 
				<feedbackInline identifier="YES" outcomeIdentifier="SHOWGENERALFB" showHide="show"><?= $scenario->feedback ?></feedbackInline>
			</prompt>
<? endif; ?>
<? foreach ($question->optionlist as $oid => $option) : ?>
            <simpleChoice fixed="false" identifier="<?=$this->ll[$oid]?>"><?= $option ?></simpleChoice>
<? endforeach; ?>
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
			
<? foreach ($question->scenarios as $sid => $scenario) : ?>
<? foreach ($scenario->correctans as $correct) : ?>
		<responseCondition>
			<responseIf>
<? if (count($scenario->correctans) == 1) : ?>
				<match>
					<variable identifier="RESPONSE<?=$this->ll[$sid]?>"/>
					<variable identifier="ANSWERS<?=$this->ll[$correct]?>"/>
				</match>
<? else : ?>
				<contains>
					<variable identifier="RESPONSE<?=$this->ll[$sid]?>"/>
					<variable identifier="ANSWERM<?=$this->ll[$correct]?>"/>
				</contains>
<? endif; ?>
				<setOutcomeValue identifier="SCORE">
					<sum>
						<variable identifier="SCORE"/>  
						<baseValue baseType="float">1</baseValue>   
					</sum>
				</setOutcomeValue>	
			</responseIf>
		</responseCondition>
		
<? endforeach; ?>
<? endforeach; ?>
	
		<setOutcomeValue identifier="SHOWGENERALFB">
			<variable identifier="GENERALFB"/>
		</setOutcomeValue>

    </responseProcessing>

</assessmentItem>
