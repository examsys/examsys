<?= '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' ?>
<assessmentTest xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1"
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	xsi:schemaLocation="http://www.imsglobal.org/xsd/imsqti_v2p1 http://www.imsglobal.org/xsd/imsqti_v2p1.xsd"
	identifier="TEST" title="<?=StripForTitle($data->questions[0]->leadin)?>">
	<outcomeDeclaration baseType="float" cardinality="single" identifier="SCORE">
		<defaultValue>
			<value>0</value>
		</defaultValue>
	</outcomeDeclaration>
	<testPart identifier="part01" navigationMode="nonlinear" submissionMode="simultaneous">
		<assessmentSection identifier="section" title="<?=StripForTitle($data->questions[0]->leadin)?>" visible="true">
<? foreach ($data->questions as $question) : ?>
			<assessmentItemRef identifier="item<?=$question->load_id?>" href="question-<?=$question->load_id?>.xml"/>
<? endforeach; ?>
		</assessmentSection>
	</testPart>
	<outcomeProcessing>
		<setOutcomeValue identifier="SCORE">
			<sum>
<? foreach ($data->questions as $question) : ?>
				<variable identifier="item<?=$question->load_id?>.SCORE" />
<? endforeach; ?>
			</sum>
		</setOutcomeValue>
	</outcomeProcessing>
</assessmentTest>
