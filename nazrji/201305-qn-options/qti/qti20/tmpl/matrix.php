<?= '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' ?>

<assessmentItem timeDependent="false" adaptive="false" label="mylabel" title="<?=$title?>" identifier="myidentifier" xsi:schemaLocation="http://www.imsglobal.org/xsd/imsqti_v2p1 imsqti_v2p1.xsd" xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" xmlns:xi="http://www.w3.org/2001/XInclude" xmlns:lip="http://www.imsglobal.org/xsd/imslip_v1p0" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:m="http://www.w3.org/1998/Math/MathML" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
	<responseDeclaration identifier="RESPONSE" cardinality="multiple" baseType="directedPair">
		<correctResponse>
<? foreach ($question->scenarios as $sid => $scenario) : ?>
			<value>S_<?= $this->ll[$sid] ?> O_<?= $this->ll[$scenario->answer] ?></value>
<? endforeach; ?>				
		</correctResponse>
		<mapping defaultValue="0">
<? foreach ($question->scenarios as $sid => $scenario) : ?>
			<mapEntry mapKey="S_<?=$this->ll[$sid]?> O_<?=$this->ll[$scenario->answer]?>" mappedValue="1"/>
<? endforeach; ?>				
		</mapping>
	</responseDeclaration>
	<outcomeDeclaration identifier="SCORE" cardinality="single" baseType="float"/>
	<itemBody>
		<?= $headertext ?>
<? if ($question->media) : ?>
		<p><object type="<?=$question->media_type?>" data="<?=$question->media?>"/></p>
<? endif; ?>
		<matchInteraction responseIdentifier="RESPONSE" shuffle="false" maxAssociations="4">
			<simpleMatchSet>
<? foreach ($question->options as $oid => $option) : ?>
				<simpleAssociableChoice identifier="O_<?=$this->ll[$oid]?>" matchMax="<?=$max_score?>"><?= $option ?></simpleAssociableChoice>
<? endforeach; ?>				
			</simpleMatchSet>
			<simpleMatchSet>
<? foreach ($question->scenarios as $sid => $scenario) : ?>
				<simpleAssociableChoice identifier="S_<?=$this->ll[$sid]?>" matchMax="1"><?= $scenario->scenario ?></simpleAssociableChoice>
<? endforeach; ?>				
			</simpleMatchSet>
		</matchInteraction>
	</itemBody>
	<responseProcessing
		template="http://www.imsglobal.org/question/qti_v2p1/rptemplates/map_response"/>
</assessmentItem>
