<?= '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' ?>

<assessmentItem timeDependent="false" adaptive="false" label="mylabel" title="<?=$title?>" identifier="myidentifier" xsi:schemaLocation="http://www.imsglobal.org/xsd/imsqti_v2p1 imsqti_v2p1.xsd" xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" xmlns:xi="http://www.w3.org/2001/XInclude" xmlns:lip="http://www.imsglobal.org/xsd/imslip_v1p0" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:m="http://www.w3.org/1998/Math/MathML" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
    <responseDeclaration baseType="identifier" cardinality="single" identifier="RESPONSE">
        <correctResponse>
            <value><?= $correctid ?></value>
        </correctResponse>
        <mapping defaultValue="0.0">
            <mapEntry mappedValue="1.0" mapKey="<?=$correctid?>"/>
        </mapping>
    </responseDeclaration>
    <outcomeDeclaration baseType="float" cardinality="single" identifier="SCORE"/>
    <outcomeDeclaration baseType="identifier" cardinality="single" identifier="FEEDBACK"/>
    <itemBody>
		<?= $headertext ?>
<? if ($question->media) : ?>
		<p><object type="<?=$question->media_type?>" data="<?=$question->media?>"/></p>
<? endif; ?>
        <choiceInteraction responseIdentifier="RESPONSE" maxChoices="1" shuffle="true">
            <prompt><?= $question->leadin ?></prompt>
<? foreach ($question->options as $oid => $option) : ?>
	        <simpleChoice fixed="false" identifier="<?=$this->ll[$oid]?>">
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
    <setOutcomeValue identifier="FEEDBACK">
      <variable identifier="RESPONSE"/>
    </setOutcomeValue>
  </responseProcessing>
  <modalFeedback outcomeIdentifier="FEEDBACK" identifier="<?=$correctid?>" showHide="show"><?= $question->fb_correct ?></modalFeedback>
  <modalFeedback outcomeIdentifier="FEEDBACK" identifier="<?=$correctid?>" showHide="hide"><?= $question->fb_incorrect ?></modalFeedback>
</assessmentItem>
