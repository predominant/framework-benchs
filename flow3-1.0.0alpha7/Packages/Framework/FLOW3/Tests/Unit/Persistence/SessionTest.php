<?php
declare(ENCODING = 'utf-8');
namespace F3\FLOW3\Persistence;

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
 * Testcase for the Persistence Session
 *
 * @version $Id: SessionTest.php 3616 2010-01-13 16:30:32Z k-fish $
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class SessionTest extends \F3\Testing\BaseTestCase {

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function objectRegisteredWithRegisterReconstitutedObjectCanBeRetrievedWithGetReconstitutedObjects() {
		$someObject = new \ArrayObject();
		$session = new \F3\FLOW3\Persistence\Session();
		$session->registerReconstitutedObject($someObject);

		$reconstitutedObjects = $session->getReconstitutedObjects();
		$this->assertTrue($reconstitutedObjects->contains($someObject));
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function unregisterReconstitutedObjectRemovesObjectFromSession() {
		$someObject = new \ArrayObject();
		$session = new \F3\FLOW3\Persistence\Session();
		$session->registerReconstitutedObject($someObject);
		$session->unregisterReconstitutedObject($someObject);

		$reconstitutedObjects = $session->getReconstitutedObjects();
		$this->assertFalse($reconstitutedObjects->contains($someObject));
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function hasObjectReturnsTrueForRegisteredObject() {
		$object1 = new \stdClass();
		$object2 = new \stdClass();
		$session = new \F3\FLOW3\Persistence\Session();
		$session->registerObject($object1, 12345);

		$this->assertTrue($session->hasObject($object1), 'Session claims it does not have registered object.');
		$this->assertFalse($session->hasObject($object2), 'Session claims it does have unregistered object.');
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function hasIdentifierReturnsTrueForRegisteredObject() {
		$object1 = new \stdClass();
		$object2 = new \stdClass();
		$session = new \F3\FLOW3\Persistence\Session();
		$session->registerObject($object1, 12345);

		$this->assertTrue($session->hasIdentifier('12345'), 'Session claims it does not have registered object.');
		$this->assertFalse($session->hasIdentifier('67890'), 'Session claims it does have unregistered object.');
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function getIdentifierByObjectReturnsRegisteredUUIDForObject() {
		$object = new \stdClass();
		$session = new \F3\FLOW3\Persistence\Session();
		$session->registerObject($object, 12345);

		$this->assertEquals($session->getIdentifierByObject($object), 12345, 'Did not get UUID registered for object.');
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function getObjectByIdentifierReturnsRegisteredObjectForUUID() {
		$object = new \stdClass();
		$session = new \F3\FLOW3\Persistence\Session();
		$session->registerObject($object, 12345);

		$this->assertSame($session->getObjectByIdentifier('12345'), $object, 'Did not get object registered for UUID.');
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function unregisterObjectRemovesRegisteredObject() {
		$object1 = new \stdClass();
		$object2 = new \stdClass();
		$session = new \F3\FLOW3\Persistence\Session();
		$session->registerObject($object1, 12345);
		$session->registerObject($object2, 67890);

		$this->assertTrue($session->hasObject($object1), 'Session claims it does not have registered object.');
		$this->assertTrue($session->hasIdentifier('12345'), 'Session claims it does not have registered object.');
		$this->assertTrue($session->hasObject($object1), 'Session claims it does not have registered object.');
		$this->assertTrue($session->hasIdentifier('67890'), 'Session claims it does not have registered object.');

		$session->unregisterObject($object1);

		$this->assertFalse($session->hasObject($object1), 'Session claims it does have unregistered object.');
		$this->assertFalse($session->hasIdentifier('12345'), 'Session claims it does not have registered object.');
		$this->assertTrue($session->hasObject($object2), 'Session claims it does not have registered object.');
		$this->assertTrue($session->hasIdentifier('67890'), 'Session claims it does not have registered object.');
	}

}
?>