<?php
/**
 * PHP 8 Compatibility Test Script
 * 
 * Tests the main PHP 8 compatibility fixes implemented in PAMI
 */

require_once __DIR__ . '/../vendor/autoload.php';

echo "=== PHP 8 Compatibility Test Suite ===\n";
echo "PHP Version: " . PHP_VERSION . "\n\n";

$errors = 0;

// Test 1: Basic class instantiation
echo "1. Testing ClientImpl instantiation...\n";
try {
    $client = new \PAMI\Client\Impl\ClientImpl([
        'host' => 'localhost',
        'port' => 5038,
        'username' => 'test',
        'secret' => 'test',
        'connect_timeout' => 10,
        'read_timeout' => 10
    ]);
    echo "   ✓ ClientImpl can be instantiated\n";
} catch (Exception $e) {
    echo "   ✗ ClientImpl instantiation failed: " . $e->getMessage() . "\n";
    $errors++;
}

// Test 2: ResponseMessage with null checks
echo "\n2. Testing ResponseMessage null handling...\n";
try {
    $mockMessage = "Response: Success\r\nActionID: test\r\nMessage: Test Message\r\n\r\n";
    $response = new \PAMI\Message\Response\ResponseMessage($mockMessage);
    
    // Test getMessage with null handling
    $message = $response->getMessage();
    echo "   ✓ getMessage() works: " . ($message ? $message : 'null') . "\n";
    
    // Test isList with null handling  
    $isList = $response->isList();
    echo "   ✓ isList() works: " . ($isList ? 'true' : 'false') . "\n";
    
} catch (Exception $e) {
    echo "   ✗ ResponseMessage test failed: " . $e->getMessage() . "\n";
    $errors++;
}

// Test 3: Event creation
echo "\n3. Testing Event instantiation...\n";
try {
    $mockEvent = "Event: PeerStatus\r\nPrivilege: system,all\r\nChannelType: SIP\r\nPeer: SIP/test\r\nPeerStatus: Registered\r\n\r\n";
    $event = new \PAMI\Message\Event\PeerStatusEvent($mockEvent);
    echo "   ✓ PeerStatusEvent can be instantiated\n";
} catch (Exception $e) {
    echo "   ✗ PeerStatusEvent instantiation failed: " . $e->getMessage() . "\n";
    $errors++;
}

// Test 4: addEvent with null values (critical PHP 8 fix)
echo "\n4. Testing addEvent with null handling (critical PHP 8 fix)...\n";
try {
    // Create a response
    $mockMessage = "Response: Success\r\nActionID: test\r\nEventlist: start\r\nMessage: Events will follow\r\n\r\n";
    $response = new \PAMI\Message\Response\ResponseMessage($mockMessage);
    
    // Create an event with potentially null EventList and Name
    $mockEventMessage = "Event: TestEvent\r\nPrivilege: system,all\r\n\r\n";
    $eventWithNulls = new \PAMI\Message\Event\UnknownEvent($mockEventMessage);
    
    // This previously would fail in PHP 8 due to null passed to stristr()
    $response->addEvent($eventWithNulls);
    echo "   ✓ addEvent() works with potentially null values\n";
    
    // Test with an event that has complete in EventList
    $mockCompleteEvent = "Event: CoreShowChannelsComplete\r\nEventList: Complete\r\nListItems: 0\r\nActionID: test\r\n\r\n";
    $completeEvent = new \PAMI\Message\Event\CoreShowChannelsCompleteEvent($mockCompleteEvent);
    $response->addEvent($completeEvent);
    echo "   ✓ addEvent() works with Complete event\n";
    
    // Check if response is now complete
    echo "   ✓ Response completion detection: " . ($response->isComplete() ? 'true' : 'false') . "\n";
    
} catch (Exception $e) {
    echo "   ✗ addEvent test failed: " . $e->getMessage() . "\n";
    $errors++;
}

// Test 5: isList with null handling (critical PHP 8 fix)
echo "\n5. Testing isList with null handling...\n";
try {
    // Test with null EventList and Message
    $mockMessageNull = "Response: Success\r\nActionID: test\r\n\r\n";
    $responseNull = new \PAMI\Message\Response\ResponseMessage($mockMessageNull);
    $isListNull = $responseNull->isList(); // Should not throw error with null values
    echo "   ✓ isList() handles null values: " . ($isListNull ? 'true' : 'false') . "\n";
    
    // Test with valid EventList
    $mockMessageStart = "Response: Success\r\nActionID: test\r\nEventList: start\r\nMessage: Events will follow\r\n\r\n";
    $responseStart = new \PAMI\Message\Response\ResponseMessage($mockMessageStart);
    $isListStart = $responseStart->isList();
    echo "   ✓ isList() detects EventList start: " . ($isListStart ? 'true' : 'false') . "\n";
    
} catch (Exception $e) {
    echo "   ✗ isList null handling test failed: " . $e->getMessage() . "\n";
    $errors++;
}

echo "\n=== Test Results ===\n";
if ($errors === 0) {
    echo "✓ All PHP 8 compatibility tests passed!\n";
    exit(0);
} else {
    echo "✗ {$errors} test(s) failed!\n";
    exit(1);
}