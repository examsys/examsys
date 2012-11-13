<?= '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' ?>
<assessmentTest xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1"
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	xsi:schemaLocation="http://www.imsglobal.org/xsd/imsqti_v2p1 http://www.imsglobal.org/xsd/imsqti_v2p1.xsd"
	identifier="TEST" title="<?=StripForTitle($paper->paper_title)?>">
	<outcomeDeclaration baseType="float" cardinality="single" identifier="SCORE">
		<defaultValue>
			<value>0</value>
		</defaultValue>
	</outcomeDeclaration>
	<testPart identifier="part01" navigationMode="nonlinear" submissionMode="simultaneous">
<? foreach ($paper->screens as $s_id => $screen) : ?>
	<assessmentSection identifier="section<?=$s_id?>" title="Screen <?=$s_id?>" visible="true">
<? foreach ($screen->question_ids as $q_id) : ?>
<? $question = FindQuestion($data->questions, $q_id); ?>
			<assessmentItemRef identifier="item<?=$question->load_id?>" href="question-<?=$question->load_id?>.xml"/>
<? endforeach; ?>
		</assessmentSection>
<? endforeach; ?>
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
