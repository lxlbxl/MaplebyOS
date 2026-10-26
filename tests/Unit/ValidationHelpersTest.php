<?php

namespace Tests\Unit;

use Modules\Operations\Services\ContainerNumber;
use Modules\Operations\Services\SmartMatcher;
use Modules\Operations\Services\TrackingIdGenerator;
use Modules\Operations\Services\VinDecoder;
use PHPUnit\Framework\TestCase;

class ValidationHelpersTest extends TestCase
{
    public function test_container_check_digit(): void
    {
        $this->assertTrue(ContainerNumber::isValid('CSQU3054383'));
        $this->assertFalse(ContainerNumber::isValid('CSQU3054384'));
        $this->assertSame('CSQU3054383', ContainerNumber::clean(' csqu 305438-3 '));
        $this->assertFalse(ContainerNumber::isWellFormed('CSQ3054383'));
    }

    public function test_vin_decode(): void
    {
        $decoded = VinDecoder::decode('1hgcm82633a004352');
        $this->assertTrue($decoded['valid']);
        $this->assertSame('1HGCM82633A004352', $decoded['vin']);
        $this->assertSame(2003, $decoded['year']);
        $this->assertSame('HONDA', $decoded['make']);

        $bad = VinDecoder::decode('1HGCM82633A00435');
        $this->assertFalse($bad['valid']);
        $this->assertNotEmpty($bad['errors']);

        $withO = VinDecoder::decode('1HGCM82633AOO4352');
        $this->assertSame('1HGCM82633A004352', $withO['suggestion']);
    }

    public function test_tracking_id_format(): void
    {
        $this->assertTrue(TrackingIdGenerator::isWellFormed('MAP26-10-023'));
        $this->assertFalse(TrackingIdGenerator::isWellFormed('MAP2610023'));
    }

    public function test_key_and_distance(): void
    {
        $this->assertSame('HAPAGLLOYD', SmartMatcher::key(' Hapag-Lloyd '));
        $this->assertSame(1, SmartMatcher::distance('CAMRY', 'CARMY'));
        $this->assertSame(1, SmartMatcher::distance('LEXUS', 'LEXUX'));
        $this->assertSame(0, SmartMatcher::distance('MSC', 'MSC'));
    }
}
