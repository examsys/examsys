<?php
// This file is part of Rogō
//
// Rogō is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogō is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogō.  If not, see <http://www.gnu.org/licenses/>.

require("header.php");
?>
			<qticomment><![CDATA[RAW_HOTSPOT:<?php echo $question->raw_option ?>]]></qticomment>

			<?php echo $headertext ?>
			<response_xy ident="IMAGE" rcardinality="Single" rtiming="No">
				<render_hotspot> 
					<material>
						<matimage imagtype="image/gif" uri="<?php echo $question->media ?>" x0="0" width="<?php echo $question->media_width ?>" y0="0" height="<?php echo $question->media_height ?>"/>
					</material>

<?php foreach($question->hotspots as $hotspot) :?>
  <?php $hid = 1; ?>
  <?php foreach ($hotspot as $subspot) : ?>
  <?php if ($subspot->type == "rectangle") : ?>
            <response_label ident="<?php echo $this->ll[$hid] ?>" rarea="Rectangle"><?php echo implode(",", $subspot->coords) ?></response_label>
  <?php elseif ($subspot->type == "ellipse") : ?>
            <response_label ident="<?php echo $this->ll[$hid] ?>" rarea="Ellipse"><?php echo(implode(",", $subspot->coords)) ?></response_label>
  <?php else : ?>
            <qticomment>Hotspot:<?php echo $subspot->type ?>|<?php echo(implode(",", $subspot->coords)) ?></qticomment>
  <?php endif; ?>
  <?php $hid++; ?>
  <?php endforeach; ?>
<?php endforeach; ?>
				</render_hotspot>
			</response_xy>
		</presentation>
		<resprocessing>
			<outcomes>
				<decvar/>
			</outcomes>
   
			<!-- force general feedback to output -->
			<respcondition title="General Feedback"  continue="Yes">
				<conditionvar>
					<or>
						<other/>
						<not>
							<other/>
						</not>
					</or>
				</conditionvar>
				<setvar action="Add"><?php $subspot->marks_incorrect?></setvar>
				<displayfeedback linkrefid="general"/>
			</respcondition>

<?php foreach($question->hotspots as $hotspot) :?>
  <?php foreach ($hotspot as $subspot) : ?>
  <?php if ($subspot->type != "rectangle" && $subspot->type != "ellipse") continue; ?>
        <respcondition>
          <conditionvar>
  <?php if ($subspot->type == "rectangle") : ?>
            <varinside respident="IMAGE" areatype="Rectangle"><?php echo(implode(",", $subspot->coords)) ?></varinside>
  <?php elseif ($subspot->type == "ellipse") : ?>
            <varinside respident="IMAGE" areatype="Ellipse"><?php echo(implode(",", $subspot->coords)) ?></varinside>
  <?php endif; ?>
          </conditionvar>
          <setvar action="Add"><?php echo $subspot->marks_correct; ?></setvar>
          <displayfeedback feedbacktype = "Response" linkrefid = "Correct"/>
        </respcondition>
  <?php endforeach; ?>
<?php endforeach; ?>
    <respcondition>
      <conditionvar>
        <other/>
      </conditionvar>
      <setvar action="Add"><?php echo $subspot->marks_incorrect; ?></setvar>
    </respcondition>
		</resprocessing>

		<!-- only 1 feedback for Hotspot questions -->
		<itemfeedback ident="General" view="Candidate">
			<material>
				<mattext texttype='text/html'><![CDATA[<?php echo $question->feedback ?>]]></mattext>
			</material>
		</itemfeedback>
	</item>
