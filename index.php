<?php
/**
 * @defgroup plugins_generic_bitsViewer submission BITS XML file plugin
 */

/**
 * @file plugins/generic/bitsViewer/index.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @ingroup plugins_generic_bitsViewer
 * @brief Wrapper for pdf submission file plugin.
 *
 */

require_once('BitsViewerPlugin.inc.php');

return new BitsViewerPlugin();


