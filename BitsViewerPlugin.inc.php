<?php

/**
 * @file plugins/generic/BitsViewerPlugin.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class BitsViewerPlugin
 * @ingroup plugins_generic_bitsViewer
 *
 * @brief Class for BitsViewer plugin
 */

import('lib.pkp.classes.plugins.GenericPlugin');

class BitsViewerPlugin extends GenericPlugin {
	/**
	 * @copydoc Plugin::register()
	 */
	function register($category, $path, $mainContextId = null) {
		if (parent::register($category, $path, $mainContextId)) {
			if ($this->getEnabled($mainContextId)) {
				$request = Application::get()->getRequest();
				$url = $request->getBaseUrl() . '/' . $this->getPluginPath() . '/styles/bitsviewer.css';
				$templateMgr = TemplateManager::getManager($request);
				$templateMgr->addStyleSheet('bitsViewerStyles', $url);

				// hook for OMP
				HookRegistry::register('CatalogBookHandler::view', array($this, 'viewCallback'), HOOK_SEQUENCE_NORMAL);
				// hook for OJS
				HookRegistry::register('ArticleHandler::view::galley', array($this, 'articleCallback'));
			}
			return true;
		}
		return false;
	}

	/**
	 * Install default settings on press creation.
	 * @return string
	 */
	function getContextSpecificPluginSettingsFile() {
		return $this->getPluginPath() . '/settings.xml';
	}

	/**
	 * Get the display name of this plugin.
	 * @return String
	 */
	function getDisplayName() {
		return __('plugins.generic.bitsViewer.displayName');
	}

	/**
	 * Get a description of the plugin.
	 */
	function getDescription() {
		return __('plugins.generic.bitsViewer.description');
	}

	/**
	 * Callback to view the XML BITS content rather than downloading.
	 * @param $hookName string
	 * @param $args array
	 * @return boolean
	 */
	function viewCallback($hookName, $args) {
		$request =& $args[0];
		$submission =& $args[1];
		$publicationFormat =& $args[2];
		$submissionFile =& $args[3];

		$mime_type = $submissionFile->getData('mimetype');

		$contextid = $this->getCurrentContextId();
		$format = $publicationFormat->getBestId();

error_log("bitsViewer::viewCallback called [".$hookName."] context[".$contextid."] submissionid[".$submission->getId()."] mimetype [".$mime_type."] format[".$format."] \n");

		if ($format == 'bits_xml' && $mime_type == 'text/xml') {
			return $this->viewBitsXmlFile($publicationFormat, $mime_type, $submission, $submissionFile);
		}

		return false;
	}


	/**
	 * Callback to view the Image content rather than downloading.
	 * @param $hookName string
	 * @param $args array
	 * @return boolean
	 */
	function articleCallback($hookName, $args) {
		$request =& $args[0];
		$submission =& $args[1];
		$galley =& $args[2];
		$submissionFile =& $args[3];

		$mime_type = $submissionFile->getData('mimetype');

		$contextid = $this->getCurrentContextId();

error_log("bitsViewer::articleCallback called [".$hookName."] context[".$contextid."] submissionid[".$submission->getId()."] mimetype [".$mime_type."] format[".$format."] \n");

		if ($format == 'iiif_manifest' && (($mime_type == 'application/json') || ($mime_type == 'text/plain'))) {
			$this->viewManifestFile($publicationFormat, $mime_type, $submission, $submissionFile);
			return true;

		} elseif ($mime_type == 'image/jpeg') {
			$this->viewImageFile($publicationFormat, $mime_type, $submission, $submissionFile);
			return true;
		}

		return false;
	}

	/**
	 * function to prepare BITS XML  data for viewing.
	 * @param $publicationFormat PublicationFormat
	 * @param $mime_type string
	 * @param $submission Submission
	 * @param $submissionFile SubmissionFile
	 * @return boolean
	 */
	function viewBitsXmlFile($publicationFormat, $mime_type, $submission, $submissionFile) {

		foreach ($submission->getData('publications') as $publication) {
			if ($publication->getId() === $publicationFormat->getData('publicationId')) {
				$filePublication = $publication;
				break;
			}
		}
#error_log("\n\n####bitsViewer::viewBitsXmlFile called found publication[".$filePublication."]\n");

		$request = Application::get()->getRequest();
		$templateMgr = TemplateManager::getManager($request);

		$fileService = Services::get('file');
		$xmlfile = $fileService->get($submissionFile->getData('fileId'));
                $contents = $fileService->fs->read($xmlfile->path);

		$fileId = $submissionFile->getId();
		$fileStage = $submissionFile->getFileStage();
		$subStageId = $submission->getStageId();
		$submissionId = $submission->getId();

		$xmlDoc = new DOMDocument();
		$xmlDoc->loadXML( $contents, LIBXML_PARSEHUGE);
		$xmlDoc->encoding = "utf-8";
		$xmlDoc->normalizeDocument();
		
#		$xsdPath = $request->getBaseUrl() .'/' .$this->getPluginPath().'/BITS-2-1-XSD/BITS-book2-1.xsd';

		#$validXML = $xmlDoc->schemaValidate($xsdPath);
		#$validXML = $xmlDoc->validate();
		#if ($validXML) {
#error_log("\n\n####bitsViewer::viewBitsXmlFile validated against[".$xsdPath."]\n");
#		} else {
#error_log("\n\n####bitsViewer::viewBitsXmlFile NOTTTTTT validated against[".$xsdPath."] [".$validXML."]\n");
#		}

		$parent = $xmlDoc->documentElement;
		if ($parent->nodeName == 'book') {


#		foreach($parent->childNodes as $anode) {
#			if ( $anode->nodeName != '#text' ) {
#				error_log("####[".$anode->nodeName."]\n");
#			}
#		}



			$templateMgr->assign(array(
				'pluginUrl' => $request->getBaseUrl() . '/' . $this->getPluginPath(),
				'isLatestPublication' => $submission->getData('currentPublicationId') === $publicationFormat->getData('publicationId'),
				'filePublication' => $filePublication,
				'subId' => $submissionId,
				'subStageId' => $subStageId,
				'domNode' => $parent,
			));

			$templateMgr->display($this->getTemplateResource('display_book.tpl'));
			return true;
		
		} elseif ($parent->nodeName == 'book-part-wrapper') {
			$templateMgr->assign(array(
				'pluginUrl' => $request->getBaseUrl() . '/' . $this->getPluginPath(),
				'isLatestPublication' => $submission->getData('currentPublicationId') === $publicationFormat->getData('publicationId'),
				'filePublication' => $filePublication,
				'subId' => $submissionId,
				'subStageId' => $subStageId,
				'domNode' => $parent,
			));

			$templateMgr->display($this->getTemplateResource('display_book_part.tpl'));
	
			return true;
		}
error_log("\n\n####bitsViewer::viewBitsXmlFile called contents[".$parent->nodeName."]\n");
//		foreach($parent->childNodes as $anode) {
//			if ( $anode->nodeName != '#text' ) {
//				error_log("####[".$anode->nodeName."]\n");
//			}
//		}

		return false;
	}


	/**
	 * Callback for download function
	 * @param $hookName string
	 * @param $params array
	 * @return boolean
	 */
	function downloadCallback($hookName, $params) {
		$submission =& $params[1];
		$publicationFormat =& $params[2];
		$submissionFile =& $params[3];
		$inline =& $params[4];
error_log("bitsViewer::downloadCallback called hookname [".$hookname."] path[".$this->getPluginPath()."]\n");

		$request = Application::get()->getRequest();
		$mimetype = $submissionFile->getData('mimetype');
		if ($mimetype == 'application/pdf' && $request->getUserVar('inline')) {
			// Turn on the inline flag to ensure that the content
			// disposition header doesn't foil the PDF embedding
			// plugin.
			$inline = true;
		}

		// Return to regular handling
		return false;
	}

	/**
	 * Get the plugin base URL.
	 * @param $request PKPRequest
	 * @return string
	 */
	private function _getPluginUrl($request) {
		return $request->getBaseUrl() . '/' . $this->getPluginPath();
	}


}


