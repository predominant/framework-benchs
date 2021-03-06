<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\Core\Rendering;

/*                                                                        *
 * This script belongs to the FLOW3 package "Fluid".                      *
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
 *
 *
 * @version $Id: RenderingContext.php 3751 2010-01-22 15:56:47Z k-fish $
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @scope prototype
 */
class RenderingContext {

	/**
	 * Template Variable Container. Contains all variables available through object accessors in the template
	 * @var F3\Fluid\Core\ViewHelper\TemplateVariableContainer
	 */
	protected $templateVariableContainer;

	/**
	 * Object factory which is bubbled through. The ViewHelperNode cannot get an ObjectFactory injected because
	 * the whole syntax tree should be cacheable
	 * @var F3\FLOW3\Object\ObjectFactoryInterface
	 */
	protected $objectFactory;

	/**
	 * Controller context being passed to the ViewHelper
	 * @var F3\FLOW3\MVC\Controller\Context
	 */
	protected $controllerContext;

	/**
	 * ViewHelper Variable Container
	 * @var F3\Fluid\Core\ViewHelpers\ViewHelperVariableContainer
	 */
	protected $viewHelperVariableContainer;

	/**
	 * Inject the object factory
	 *
	 * @param F3\FLOW3\Object\ObjectFactoryInterface $objectFactory
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function injectObjectFactory(\F3\FLOW3\Object\ObjectFactoryInterface $objectFactory) {
		$this->objectFactory = $objectFactory;
	}

	/**
	 * Returns the object factory. Only the ViewHelperNode should do this.
	 *
	 * @param F3\FLOW3\Object\ObjectFactoryInterface $objectFactory
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function getObjectFactory() {
		return $this->objectFactory;
	}

	/**
	 * Sets the template variable container containing all variables available through ObjectAccessors
	 * in the template
	 *
	 * @param F3\Fluid\Core\ViewHelper\TemplateVariableContainer $templateVariableContainer The template variable container to set
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function setTemplateVariableContainer(\F3\Fluid\Core\ViewHelper\TemplateVariableContainer $templateVariableContainer) {
		$this->templateVariableContainer = $templateVariableContainer;
	}

	/**
	 * Get the template variable container
	 *
	 * @return F3\Fluid\Core\ViewHelper\TemplateVariableContainer The Template Variable Container
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function getTemplateVariableContainer() {
		return $this->templateVariableContainer;
	}

	/**
	 * Set the controller context which will be passed to the ViewHelper
	 *
	 * @param F3\FLOW3\MVC\Controller\Context $controllerContext The controller context to set
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function setControllerContext(\F3\FLOW3\MVC\Controller\Context $controllerContext) {
		$this->controllerContext = $controllerContext;
	}

	/**
	 * Get the controller context which will be passed to the ViewHelper
	 *
	 * @return F3\FLOW3\MVC\Controller\Context The controller context to set
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function getControllerContext() {
		return $this->controllerContext;
	}

	/**
	 * Set the ViewHelperVariableContainer
	 *
	 * @param F3\Fluid\Core\ViewHelper\ViewHelperVariableContainer $viewHelperVariableContainer
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function setViewHelperVariableContainer(\F3\Fluid\Core\ViewHelper\ViewHelperVariableContainer $viewHelperVariableContainer) {
		$this->viewHelperVariableContainer = $viewHelperVariableContainer;
	}

	/**
	 * Get the ViewHelperVariableContainer
	 *
	 * @return F3\Fluid\Core\ViewHelper\ViewHelperVariableContainer
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function getViewHelperVariableContainer() {
		return $this->viewHelperVariableContainer;
	}
}
?>