<?php
declare(ENCODING = 'utf-8');
namespace F3\Fluid\Core\Parser;

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
 * Testcase for TemplateParser.
 *
 * This is to at least half a system test, as it compares rendered results to
 * expectations, and does not strictly check the parsing...
 *
 * @version $Id: TemplateParserTest.php 3751 2010-01-22 15:56:47Z k-fish $
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class TemplateParserTest extends \F3\Testing\BaseTestCase {

	/**
	 * @var F3\Fluid\Core\Parser\TemplateParser
	 */
	protected $templateParser;

	/**
	 * @var F3\Fluid\Core\Rendering\RenderingContext
	 */
	protected $renderingContext;

	/**
	 * Sets up this test case
	 *
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function setUp() {
		$this->templateParser = new \F3\Fluid\Core\Parser\TemplateParser();
		$this->templateParser->injectObjectFactory($this->objectFactory);

		$this->renderingContext = new \F3\Fluid\Core\Rendering\RenderingContext();
		$this->renderingContext->injectObjectFactory($this->objectFactory);
		$this->renderingContext->setControllerContext($this->getMock('F3\FLOW3\MVC\Controller\Context', array(), array(), '', FALSE));
		$this->renderingContext->setViewHelperVariableContainer(new \F3\Fluid\Core\ViewHelper\ViewHelperVariableContainer());
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.or>
	 * @expectedException \F3\Fluid\Core\Parser\Exception
	 */
	public function parseThrowsExceptionWhenStringArgumentMissing() {
		$this->templateParser->parse(123);
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function parseExtractsNamespacesCorrectly() {
		$this->templateParser->parse(" \{namespace f4=F7\Rocks} {namespace f4=F3\Rocks\Really}");
		$expected = array(
			'f' => 'F3\Fluid\ViewHelpers',
			'f4' => 'F3\Rocks\Really'
		);
		$this->assertEquals($this->templateParser->getNamespaces(), $expected, 'Namespaces do not match.');
	}

	/**
	 * @test
	 * @author Jochen Rau <jochen.rau@typoplanet.de>
	 */
	public function viewHelperNameCanBeResolved() {
		$mockTemplateParser = $this->getMock($this->buildAccessibleProxy('F3\Fluid\Core\Parser\TemplateParser'), array('dummy'), array(), '', FALSE);
		$result = $mockTemplateParser->_call('resolveViewHelperName', 'f', 'foo.bar.baz');
		$expected = 'F3\Fluid\ViewHelpers\Foo\Bar\BazViewHelper';
		$this->assertEquals($result, $expected, 'The name of the View Helper Name could not be resolved.');
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @expectedException \F3\Fluid\Core\Parser\Exception
	 */
	public function parseThrowsExceptionIfNamespaceIsRedeclared() {
		$this->templateParser->parse("{namespace f3=F3\Fluid\Blablubb} {namespace f3= F3\Rocks\Blu}");
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function fixture01ReturnsCorrectObjectTree($file = '/Fixtures/TemplateParserTestFixture01.html') {
		$templateSource = file_get_contents(__DIR__ . $file, FILE_TEXT);

		$expected = new \F3\Fluid\Core\Parser\SyntaxTree\RootNode();
		$expected->addChildNode(new \F3\Fluid\Core\Parser\SyntaxTree\TextNode("\na"));
		$viewHelper = $this->objectFactory->create('F3\Fluid\ViewHelpers\BaseViewHelper');
		$viewHelper->prepareArguments();
		$dynamicNode = new \F3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode($viewHelper, array());
		$expected->addChildNode($dynamicNode);
		$expected->addChildNode(new \F3\Fluid\Core\Parser\SyntaxTree\TextNode('b'));

		$actual = $this->templateParser->parse($templateSource)->getRootNode();
		$this->assertEquals($expected, $actual, 'Fixture 01 was not parsed correctly.');
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function fixture01ShorthandSyntaxReturnsCorrectObjectTree() {
		$this->fixture01ReturnsCorrectObjectTree('/Fixtures/TemplateParserTestFixture01-shorthand.html');
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function fixture02ReturnsCorrectObjectTree($file = '/Fixtures/TemplateParserTestFixture02.html') {
		$templateSource = file_get_contents(__DIR__ . $file, FILE_TEXT);

		$expected = new \F3\Fluid\Core\Parser\SyntaxTree\RootNode();
		$expected->addChildNode(new \F3\Fluid\Core\Parser\SyntaxTree\TextNode("\n"));
		$viewHelper = $this->objectFactory->create('F3\Fluid\ViewHelpers\BaseViewHelper');
		$viewHelper->prepareArguments();
		$dynamicNode = new \F3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode($viewHelper, array());
		$expected->addChildNode($dynamicNode);
		$expected->addChildNode(new \F3\Fluid\Core\Parser\SyntaxTree\TextNode("\n"));
		$viewHelper = $this->objectFactory->create('F3\Fluid\ViewHelpers\BaseViewHelper');
		$viewHelper->prepareArguments();
		$dynamicNode = new \F3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode($viewHelper, array());
		$expected->addChildNode($dynamicNode);

		$actual = $this->templateParser->parse($templateSource)->getRootNode();
		$this->assertEquals($expected, $actual, 'Fixture 02 was not parsed correctly.');
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function fixture02ShorthandSyntaxReturnsCorrectObjectTree() {
		$this->fixture02ReturnsCorrectObjectTree('/Fixtures/TemplateParserTestFixture02-shorthand.html');
	}

	/**
	 * @test
	 * @expectedException \F3\Fluid\Core\Parser\Exception
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function fixture03ThrowsExceptionBecauseOfWrongTagNesting() {
		$templateSource = file_get_contents(__DIR__ . '/Fixtures/TemplateParserTestFixture03.html', FILE_TEXT);
		$this->templateParser->parse($templateSource);
	}

	/**
	 * @test
	 * @expectedException \F3\Fluid\Core\Parser\Exception
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function fixture04ThrowsExceptionBecauseOfClosingATagWhichWasNeverOpened() {
		$templateSource = file_get_contents(__DIR__ . '/Fixtures/TemplateParserTestFixture04.html', FILE_TEXT);
		$this->templateParser->parse($templateSource);
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function fixture05ReturnsCorrectObjectTree() {
		$templateSource = file_get_contents(__DIR__ . '/Fixtures/TemplateParserTestFixture05.html', FILE_TEXT);

		$expected = new \F3\Fluid\Core\Parser\SyntaxTree\RootNode();
		$expected->addChildNode(new \F3\Fluid\Core\Parser\SyntaxTree\TextNode("\na"));
		$dynamicNode = new \F3\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode('posts.bla.Testing3');
		$expected->addChildNode($dynamicNode);
		$expected->addChildNode(new \F3\Fluid\Core\Parser\SyntaxTree\TextNode('b'));

		$actual = $this->templateParser->parse($templateSource)->getRootNode();
		$this->assertEquals($expected, $actual, 'Fixture 05 was not parsed correctly.');
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function fixture06ReturnsCorrectObjectTree($file = '/Fixtures/TemplateParserTestFixture06.html') {
		$templateSource = file_get_contents(__DIR__ . $file, FILE_TEXT);

		$expected = new \F3\Fluid\Core\Parser\SyntaxTree\RootNode();

		$viewHelper = $this->objectFactory->create('F3\Fluid\ViewHelpers\Format\Nl2brViewHelper');
		$viewHelper->prepareArguments();
		$dynamicNode1 = new \F3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode($viewHelper, array());
		$expected->addChildNode($dynamicNode1);

		$viewHelper = $this->objectFactory->create('F3\Fluid\ViewHelpers\Format\NumberViewHelper');
		$viewHelper->prepareArguments();
		$arguments = array(
			'decimals' => new \F3\Fluid\Core\Parser\SyntaxTree\TextNode('1')
		);
		$dynamicNode2 = new \F3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode($viewHelper, $arguments);
		$dynamicNode1->addChildNode($dynamicNode2);
		$dynamicNode2->addChildNode(new \F3\Fluid\Core\Parser\SyntaxTree\ObjectAccessorNode('number'));

		$actual = $this->templateParser->parse($templateSource)->getRootNode();
		$this->assertEquals($expected, $actual, 'Fixture 06 was not parsed correctly.');
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function fixture06InlineNotationReturnsCorrectObjectTree() {
		$this->fixture06ReturnsCorrectObjectTree('/Fixtures/TemplateParserTestFixture06-shorthand.html');
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function fixture07ReturnsCorrectlyRenderedResult() {
		$templateSource = file_get_contents(__DIR__ . '/Fixtures/TemplateParserTestFixture07.html', FILE_TEXT);

		$templateVariableContainer = new \F3\Fluid\Core\ViewHelper\TemplateVariableContainer(array('id' => 1));

		$this->renderingContext->setTemplateVariableContainer($templateVariableContainer);

		$parsedTemplate = $this->templateParser->parse($templateSource);
		$result = $parsedTemplate->render($this->renderingContext);
		$expected = '1';
		$this->assertEquals($expected, $result, 'Fixture 07 was not parsed correctly.');
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function fixture08ReturnsCorrectlyRenderedResult() {
		$templateSource = file_get_contents(__DIR__ . '/Fixtures/TemplateParserTestFixture08.html', FILE_TEXT);

		$variableContainer = new \F3\Fluid\Core\ViewHelper\TemplateVariableContainer(array('idList' => array(0, 1, 2, 3, 4, 5)));
		$this->renderingContext->setTemplateVariableContainer($variableContainer);

		$parsedTemplate = $this->templateParser->parse($templateSource);
		$result = $parsedTemplate->render($this->renderingContext);

		$expected = '0 1 2 3 4 5 ';
		$this->assertEquals($expected, $result, 'Fixture 08 was not rendered correctly.');
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function fixture09ReturnsCorrectlyRenderedResult() {
		$templateSource = file_get_contents(__DIR__ . '/Fixtures/TemplateParserTestFixture09.html', FILE_TEXT);

		$variableContainer = new \F3\Fluid\Core\ViewHelper\TemplateVariableContainer(array('idList' => array(0, 1, 2, 3, 4, 5), 'variableName' => 3));
		$this->renderingContext->setTemplateVariableContainer($variableContainer);

		$parsedTemplate = $this->templateParser->parse($templateSource);
		$result = $parsedTemplate->render($this->renderingContext);

		$expected = '0 hallo test 3 4 ';
		$this->assertEquals($expected, $result, 'Fixture 09 was not rendered correctly. This is most likely due to problems in the array parser.');
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function fixture10ReturnsCorrectlyRenderedResult() {
		$templateSource = file_get_contents(__DIR__ . '/Fixtures/TemplateParserTestFixture10.html', FILE_TEXT);

		$variableContainer = new \F3\Fluid\Core\ViewHelper\TemplateVariableContainer(array('idList' => array(0, 1, 2, 3, 4, 5)));
		$this->renderingContext->setTemplateVariableContainer($variableContainer);

		$parsedTemplate = $this->templateParser->parse($templateSource);
		$result = $parsedTemplate->render($this->renderingContext);

		$expected = '0 1 2 3 4 5 ';
		$this->assertEquals($expected, $result, 'Fixture 10 was not rendered correctly. This has proboably something to do with line breaks inside tags.');
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function fixture11ReturnsCorrectlyRenderedResult() {
		$templateSource = file_get_contents(__DIR__ . '/Fixtures/TemplateParserTestFixture11.html', FILE_TEXT);

		$variableContainer = new \F3\Fluid\Core\ViewHelper\TemplateVariableContainer(array());
		$this->renderingContext->setTemplateVariableContainer($variableContainer);

		$parsedTemplate = $this->templateParser->parse($templateSource);
		$result = $parsedTemplate->render($this->renderingContext);

		$expected = '0 2 4 ';
		$this->assertEquals($expected, $result, 'Fixture 11 was not rendered correctly.');
	}

	/**
	 * Test for CDATA support
	 *
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function fixture12ReturnsCorrectlyRenderedResult() {
		$templateSource = file_get_contents(__DIR__ . '/Fixtures/TemplateParserTestFixture12_cdata.html', FILE_TEXT);

		$variableContainer = new \F3\Fluid\Core\ViewHelper\TemplateVariableContainer(array());
		$this->renderingContext->setTemplateVariableContainer($variableContainer);

		$parsedTemplate = $this->templateParser->parse($templateSource);
		$result = $parsedTemplate->render($this->renderingContext);

		$expected = '<f3:for each="{a: {a: 0, b: 2, c: 4}}" as="array">' . chr(10) . '<f3:for each="{array}" as="value">{value} </f3:for>';
		$this->assertEquals($expected, $result, 'Fixture 12 was not rendered correctly. This hints at some problem with CDATA handling.');
	}

	/**
	 * Test for CDATA support
	 *
	 * @test
	 * @expectedException F3\Fluid\Core\Parser\Exception
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function fixture13ThrowsExceptionBecauseOfWrongArgument() {
		$templateSource = file_get_contents(__DIR__ . '/Fixtures/TemplateParserTestFixture13_mandatoryInformation.html', FILE_TEXT);

		$templateTree = $this->templateParser->parse($templateSource)->getRootNode();
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function postParseFacetIsCalledOnParse() {
		$this->markTestIncomplete('this test need to be fixed');

		$templateParser = new \F3\Fluid\Core\Parser\TemplateParser();
		$templateParser->injectObjectFactory($this->objectFactory);

		$templateSource = file_get_contents(__DIR__ . '/Fixtures/TemplateParserTestPostParseFixture.html', FILE_TEXT);
		$templateTree = $templateParser->parse($templateSource)->getRootNode();
		$this->assertTrue(F3\Fluid\Core\Fixtures\PostParseFacetViewHelper::$wasCalled, 'PostParse was not called!');
	}

	/**
	 * @test
	 * @expectedException F3\Fluid\Core\Parser\Exception
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function abortIfUnregisteredArgumentsExist() {
		$mockTemplateParser = $this->getMock($this->buildAccessibleProxy('F3\Fluid\Core\Parser\TemplateParser'), array('dummy'), array(), '', FALSE);
		$expectedArguments = array(
			new \F3\Fluid\Core\ViewHelper\ArgumentDefinition('name1', 'string', 'desc', TRUE),
			new \F3\Fluid\Core\ViewHelper\ArgumentDefinition('name2', 'string', 'desc', TRUE)
		);
		$actualArguments = array(
			'name1' => 'bla',
			'name4' => 'bla'
		);
		$mockTemplateParser->_call('abortIfUnregisteredArgumentsExist', $expectedArguments, $actualArguments);
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function makeSureThatAbortIfUnregisteredArgumentsExistDoesNotThrowExceptionIfEverythingIsOk() {
		$mockTemplateParser = $this->getMock($this->buildAccessibleProxy('F3\Fluid\Core\Parser\TemplateParser'), array('dummy'), array(), '', FALSE);
		$expectedArguments = array(
			new \F3\Fluid\Core\ViewHelper\ArgumentDefinition('name1', 'string', 'desc', TRUE),
			new \F3\Fluid\Core\ViewHelper\ArgumentDefinition('name2', 'string', 'desc', TRUE)
		);
		$actualArguments = array(
			'name1' => 'bla'
		);
		$mockTemplateParser->_call('abortIfUnregisteredArgumentsExist', $expectedArguments, $actualArguments);
	}

	/**
	 * @test
	 * @expectedException F3\Fluid\Core\Parser\Exception
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function abortIfRequiredArgumentsAreMissingShouldThrowExceptionIfRequiredArgumentIsMissing() {
		$mockTemplateParser = $this->getMock($this->buildAccessibleProxy('F3\Fluid\Core\Parser\TemplateParser'), array('dummy'), array(), '', FALSE);
		$expectedArguments = array(
			new \F3\Fluid\Core\ViewHelper\ArgumentDefinition('name1', 'string', 'desc', TRUE),
			new \F3\Fluid\Core\ViewHelper\ArgumentDefinition('name2', 'string', 'desc', FALSE)
		);
		$actualArguments = array(
			'name2' => 'bla'
		);
		$mockTemplateParser->_call('abortIfRequiredArgumentsAreMissing', $expectedArguments, $actualArguments);
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function abortIfRequiredArgumentsAreMissingShouldNotThrowExceptionIfRequiredArgumentIsNotMissing() {
		$mockTemplateParser = $this->getMock($this->buildAccessibleProxy('F3\Fluid\Core\Parser\TemplateParser'), array('dummy'), array(), '', FALSE);
		$expectedArguments = array(
			new \F3\Fluid\Core\ViewHelper\ArgumentDefinition('name1', 'string', 'desc', FALSE),
			new \F3\Fluid\Core\ViewHelper\ArgumentDefinition('name2', 'string', 'desc', FALSE)
		);
		$actualArguments = array(
			'name2' => 'bla'
		);
		$mockTemplateParser->_call('abortIfRequiredArgumentsAreMissing', $expectedArguments, $actualArguments);
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function fixture14ReturnsCorrectObjectTree($file = '/Fixtures/TemplateParserTestFixture14.html') {
		$templateSource = file_get_contents(__DIR__ . $file, FILE_TEXT);

		$expected = new \F3\Fluid\Core\Parser\SyntaxTree\RootNode();
		$arguments = array(
			'arguments' => new \F3\Fluid\Core\Parser\SyntaxTree\RootNode(),
		);
		$arguments['arguments']->addChildNode(new \F3\Fluid\Core\Parser\SyntaxTree\ArrayNode(array('number' => 362525200)));

		$viewHelper = $this->objectFactory->create('F3\Fluid\ViewHelpers\Format\PrintfViewHelper');
		$viewHelper->prepareArguments();
		$dynamicNode = new \F3\Fluid\Core\Parser\SyntaxTree\ViewHelperNode($viewHelper, $arguments);
		$expected->addChildNode($dynamicNode);
		$dynamicNode->addChildNode(new \F3\Fluid\Core\Parser\SyntaxTree\TextNode('%.3e'));

		$actual = $this->templateParser->parse($templateSource)->getRootNode();
		$this->assertEquals($expected, $actual, 'Fixture 14 was not parsed correctly.');
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function resolveViewHelperNameWorksWithMoreThanOneLevel() {
		$mockTemplateParser = $this->getMock($this->buildAccessibleProxy('F3\Fluid\Core\Parser\TemplateParser'), array('dummy'), array(), '', FALSE);
		$actual = $mockTemplateParser->_call('resolveViewHelperName', 'f', 'my.multi.level');
		$expected = 'F3\Fluid\ViewHelpers\My\Multi\LevelViewHelper';
		$this->assertEquals($expected, $actual, 'View Helper resolving does not support multiple nesting levels.');
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function valuesFromObjectAccessorsAreRunThroughValueInterceptors() {
		$this->markTestIncomplete();
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function viewHelperArgumentsAreNotRunThroughValueInterceptors() {
		$this->markTestIncomplete();
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function textNodesAreRunThroughTextInterceptors() {
		$this->markTestIncomplete();
	}
}

?>