<?= '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' ?>
<manifest xmlns="http://www.imsglobal.org/xsd/imscp_v1p1"
	xmlns:imsmd="http://www.imsglobal.org/xsd/imsmd_v1p2"
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	xmlns:imsqti="http://www.imsglobal.org/xsd/imsqti_v2p1"
	identifier="MANIFEST-85D76736-6D19-9DC0-7C0B-57C31A9FD397"
	xsi:schemaLocation="http://www.imsglobal.org/xsd/imscp_v1p1 http://www.imsglobal.org/xsd/imscp_v1p2.xsd 
http://www.imsglobal.org/xsd/imsmd_v1p2 http://www.imsglobal.org/xsd/imsmd_v1p2p4.xsd http://www.imsglobal.org/xsd/imsqti_v2p1 http://www.imsglobal.org/xsd/imsqti_v2p1.xsd">
	<metadata>
		<schema>IMS Content</schema>
		<schemaversion>1.2</schemaversion>
		<imsmd:lom>
			<imsmd:general>
				<imsmd:title>
					<imsmd:langstring xml:lang="en"><?= StripForTitle($data->questions[0]->leadin) ?></imsmd:langstring>
				</imsmd:title>
				<imsmd:language>en</imsmd:language>
				<imsmd:description>
					<imsmd:langstring xml:lang="en"><?= StripForTitle($data->questions[0]->scenario) ?></imsmd:langstring>
				</imsmd:description>
			</imsmd:general>
			<imsmd:lifecycle>
				<imsmd:version>
					<imsmd:langstring xml:lang="en">1.0</imsmd:langstring>
				</imsmd:version>
				<imsmd:status>
					<imsmd:source>
						<imsmd:langstring xml:lang="en">LOMv1.0</imsmd:langstring>
					</imsmd:source>
					<imsmd:value>
						<imsmd:langstring xml:lang="x-none">Final</imsmd:langstring>
					</imsmd:value>
				</imsmd:status>
			</imsmd:lifecycle>
			<imsmd:metametadata>
				<imsmd:metadatascheme>LOMv1.0</imsmd:metadatascheme>
				<imsmd:metadatascheme>QTIv2.1</imsmd:metadatascheme>
				<imsmd:language>en</imsmd:language>
			</imsmd:metametadata>
			<imsmd:technical>
				<imsmd:format>text/x-imsqti-test-xml</imsmd:format>
				<imsmd:format>text/x-imsqti-item-xml</imsmd:format>
				<imsmd:format>image/png</imsmd:format>
				<imsmd:format>image/jpg</imsmd:format>
				<imsmd:format>image/jpeg</imsmd:format>
				<imsmd:format>image/gif</imsmd:format>
			</imsmd:technical>
			<imsmd:rights>
				<imsmd:description>
					<imsmd:langstring xml:lang="en"></imsmd:langstring>
				</imsmd:description>
			</imsmd:rights>
		</imsmd:lom>
	</metadata>
	<organizations/>
	<resources>
	<!-- TEST RESOURCE -->
		<resource identifier="RES-TEST"
			type="imsqti_test_xmlv2p1" href="test.xml">
			<metadata>
				<imsmd:lom>
					<imsmd:general>
						<imsmd:title>
							<imsmd:langstring xml:lang="en"><?= StripForTitle($data->questions[0]->leadin) ?></imsmd:langstring>
						</imsmd:title>
						<imsmd:language>en</imsmd:language>
						<imsmd:description>
							<imsmd:langstring xml:lang="en"><?= StripForTitle($data->questions[0]->scenario) ?></imsmd:langstring>
						</imsmd:description>
					</imsmd:general>
					<imsmd:lifecycle>
						<imsmd:version>
							<imsmd:langstring xml:lang="en">1.0</imsmd:langstring>
						</imsmd:version>
						<imsmd:status>
							<imsmd:source>
								<imsmd:langstring xml:lang="en">LOMv1.0</imsmd:langstring>
							</imsmd:source>
							<imsmd:value>
								<imsmd:langstring xml:lang="x-none">Final</imsmd:langstring>
							</imsmd:value>
						</imsmd:status>
					</imsmd:lifecycle>
					<imsmd:metametadata>
						<imsmd:metadatascheme>LOMv1.0</imsmd:metadatascheme>
						<imsmd:language>en</imsmd:language>
					</imsmd:metametadata>
					<imsmd:technical>
						<imsmd:format>text/x-imsqti-test-xml</imsmd:format>
					</imsmd:technical>
					<imsmd:rights>
						<imsmd:description>
							<imsmd:langstring xml:lang="en">(c) 2006, IMS Global Learning
								Consortium; individual questions may have own copyright
							statement.</imsmd:langstring>
						</imsmd:description>
					</imsmd:rights>
				</imsmd:lom>
			</metadata>
			<file href="test.xml"/>
<? foreach ($data->questions as $question) : ?>
			<dependency identifierref="RES-<?=$question->load_id?>"/>
<? endforeach; ?>
		</resource>
		
		<!-- QUESTION RESOURCE -->
<? foreach ($data->questions as $question) : ?>
		<resource identifier="RES-<?=$question->load_id?>" type="imsqti_item_xmlv2p1"
			href="question-<?=$question->load_id?>.xml">
			<metadata>
				<imsmd:lom>
					<imsmd:general>
						<imsmd:identifier><?= $question->load_id ?></imsmd:identifier>
						<imsmd:title>
							<imsmd:langstring xml:lang="en"><?= StripForTitle($question->leadin) ?></imsmd:langstring>
						</imsmd:title>
						<imsmd:description>
							<imsmd:langstring xml:lang="en"><?= StripForTitle($question->scenario) ?></imsmd:langstring>
						</imsmd:description>
					</imsmd:general>
					<imsmd:lifecycle>
						<imsmd:version>
							<imsmd:langstring xml:lang="en">1.0</imsmd:langstring>
						</imsmd:version>
						<imsmd:status>
							<imsmd:source>
								<imsmd:langstring xml:lang="x-none">LOMv1.0</imsmd:langstring>
							</imsmd:source>
							<imsmd:value>
								<imsmd:langstring xml:lang="x-none">Draft</imsmd:langstring>
							</imsmd:value>
						</imsmd:status>
					</imsmd:lifecycle>
					<imsmd:technical>
						<imsmd:format>text/x-imsqti-item-xml</imsmd:format>
						<imsmd:format>image/png</imsmd:format>
						<imsmd:format>image/jpg</imsmd:format>
						<imsmd:format>image/jpeg</imsmd:format>
						<imsmd:format>image/gif</imsmd:format>
					</imsmd:technical>
				</imsmd:lom>
				<imsqti:qtiMetadata>
					<!--<imsqti:timeDependent>false</imsqti:timeDependent>
					<imsqti:interactionType>choiceInteraction</imsqti:interactionType>
					<imsqti:feedbackType>adaptive</imsqti:feedbackType>
					<imsqti:solutionAvailable>true</imsqti:solutionAvailable>-->
				</imsqti:qtiMetadata>
			</metadata>
<? foreach ($data->files as $file) : ?>
<? if ($file->id != $question->load_id) continue; ?>
<? //if (strtolower(substr($file->filename,strlen($file->filename)-3,3)) == "xml") continue; ?>
			<file href="<?=$file->filename?>"/>
<? endforeach; ?>
		</resource>
<? endforeach; ?>
	</resources>
</manifest>
