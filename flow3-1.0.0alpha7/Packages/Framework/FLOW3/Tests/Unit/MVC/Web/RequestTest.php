<?php
declare(ENCODING = 'utf-8');
namespace F3\FLOW3\MVC\Web;

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
 * Testcase for the MVC Web Request class
 *
 * @version $Id: RequestTest.php 3643 2010-01-15 14:38:07Z robert $
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class RequestTest extends \F3\Testing\BaseTestCase {

	/**
	 * @var \F3\FLOW3\MVC\Web\Request
	 */
	protected $request;

	/**
	 * @var \F3\FLOW3\Property\DataType\Uri
	 */
	protected $requestUri;

	/**
	 * Sets up this test case
	 *
	 * @author  Robert Lemke <robert@typo3.org>
	 */
	protected function setUp() {
		$this->environment = new \F3\FLOW3\Utility\MockEnvironment();
		$this->environment->SERVER['ORIG_SCRIPT_NAME'] = '/path1/path2/index.php';
		$this->environment->SERVER['SCRIPT_NAME'] = '/path1/path2/index.php';

		$uriString = 'http://username:password@subdomain.domain.com:8080/path1/path2/index.php?argument1=value1&argument2=value2#anchor';
		$this->requestUri = new \F3\FLOW3\Property\DataType\Uri($uriString);
	}

	/**
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function getArgumentsReturnsProperlyInitializedArgumentsArrayForNewRequest() {
		$request = new \F3\FLOW3\MVC\Web\Request();
		$this->assertType('array', $request->getArguments(), 'getArguments() does not return an array for a virgin request object.');
	}

	/**
	 * Checks if the request URI is returned as expected.
	 *
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function getRequestUriReturnsCorrectUri() {
		$request = new \F3\FLOW3\MVC\Web\Request();
		$request->injectEnvironment($this->environment);
		$request->setRequestUri($this->requestUri);

		$this->assertEquals($this->requestUri, $request->getRequestUri(), 'request->getRequestUri() did not return the expected URI.');
		$this->assertNotSame($this->requestUri, $request->getRequestUri(), 'request->getRequestUri() returned the same URI which is dangerous ...');
	}

	/**
	 * Checks if the test URI is detected correctly as the base URI
	 *
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function getBaseUriDetectsSimpleUriCorrectly() {
		$this->environment->SERVER['ORIG_SCRIPT_NAME'] = NULL;
		$this->environment->SERVER['SCRIPT_NAME'] = '/';

		$requestUri = new \F3\FLOW3\Property\DataType\Uri('http://www.server.com/index.php');
		$expectedBaseUri = new \F3\FLOW3\Property\DataType\Uri('http://www.server.com/');

		$request = new \F3\FLOW3\MVC\Web\Request();
		$request->injectEnvironment($this->environment);
		$request->setRequestUri($requestUri);

		$this->assertEquals($expectedBaseUri, $request->getBaseUri(), 'The returned baseUri is not as expected.');
	}

	/**
	 * Checks if the base URI is detected correctly when TYPO3 resides in a subdirectory of the web root.
	 *
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function getBaseUriDetectsUriWithSubDirectoryCorrectly() {
		$this->environment->SERVER['ORIG_SCRIPT_NAME'] = NULL;
		$this->environment->SERVER['SCRIPT_NAME'] = '/path1/path2/index.php';

		$requestUri = new \F3\FLOW3\Property\DataType\Uri('http://www.server.com/path1/path2/index.php');
		$expectedBaseUri = new \F3\FLOW3\Property\DataType\Uri('http://www.server.com/path1/path2/');

		$request = new \F3\FLOW3\MVC\Web\Request();
		$request->injectEnvironment($this->environment);
		$request->setRequestUri($requestUri);

		$this->assertEquals($expectedBaseUri, $request->getBaseUri(), 'The returned baseUri is not as expected.');
	}

	/**
	 * @test
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function theRequestMethodCanBeSetAndRetrieved() {
		$request = new \F3\FLOW3\MVC\Web\Request();

		$request->setMethod('GET');
		$this->assertEquals('GET', $request->getMethod());

		$request->setMethod('POST');
		$this->assertEquals('POST', $request->getMethod());
	}

	/**
	 * @test
	 * @expectedException \F3\FLOW3\MVC\Exception\InvalidRequestMethodException
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function requestMethodsWhichAreNotCompletelyUpperCaseAreRejected() {
		$request = new \F3\FLOW3\MVC\Web\Request();
		$request->setMethod('sOmEtHing');
	}

	/**
	 * @test
	 * @author Christopher Hlubek <hlubek@networkteam.com>
	 */
	public function errorsCanBeSetAndRetrieved() {
		$errors = array(new \stdClass());

		$request = new \F3\FLOW3\MVC\Web\Request();

		$request->setErrors($errors);
		$this->assertEquals($errors, $request->getErrors());
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org
	 */
	public function hmacVerifiedCanBeSetAndRetrieved() {
		$request = new \F3\FLOW3\MVC\Web\Request();

		$this->assertFalse($request->isHmacVerified());

		$request->setHmacVerified(TRUE);
		$this->assertTrue($request->isHmacVerified());

		$request->setHmacVerified(FALSE);
		$this->assertFalse($request->isHmacVerified());
	}
}
?>