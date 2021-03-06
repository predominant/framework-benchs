<?php
declare(ENCODING = 'utf-8');
namespace F3\FLOW3\MVC\View;

/*                                                                        *
 * This script belongs to the FLOW3 framework.                            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * The default view - a special case.
 *
 * @version $Id: StandardView.php 3449 2009-11-05 13:41:57Z k-fish $
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @api
 */
final class StandardView extends \F3\FLOW3\MVC\View\AbstractView {
	/**
	 * Renders the default view
	 *
	 * @return string The rendered view
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 * @throws \F3\FLOW3\MVC\Exception if no request has been set
	 * @api
	 */
	public function render() {
		if (!is_object($this->controllerContext->getRequest())) throw new \F3\FLOW3\MVC\Exception('Can\'t render view without request object.', 1192450280);
		$template = file_get_contents($this->getTemplatePathAndFilename());
		if ($this->controllerContext->getRequest() instanceof \F3\FLOW3\MVC\Web\Request) {
			$template = str_replace('###BASEURI###', $this->controllerContext->getRequest()->getBaseUri(), $template);
		}
		return $template;
	}

	/**
	 * Retrieves path and filename of the standard-template
	 *
	 * @return string path and filename of the standard-template
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	protected function getTemplatePathAndFilename() {
		return FLOW3_PATH_FLOW3 . 'Resources/Private/MVC/StandardView_Template.html';
	}
}

?>