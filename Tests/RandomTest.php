<?php
/**
 * Ryan's Random Data Library
 *
 * @package Rych\Random
 * @author Ryan Chouinard <rchouinard@gmail.com>
 * @copyright Copyright (c) 2013, Ryan Chouinard
 * @license MIT License - http://www.opensource.org/licenses/mit-license.php
 */

namespace Rych\Random\Tests;

use Rych\Random\Generator\OpenSSLGenerator;
use Rych\Random\Random;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Main class tests
 *
 * @package Rych\Random
 * @author Ryan Chouinard <rchouinard@gmail.com>
 * @copyright Copyright (c) 2013, Ryan Chouinard
 * @license MIT License - http://www.opensource.org/licenses/mit-license.php
 */
class RandomTest extends TestCase
{

    /**
     * @test
     * @return void
     */
    public function testConstructorTakesGeneratorAndEncoder()
    {
        $generator = new \Rych\Random\Generator\MockGenerator;
        $encoder = new \Rych\Random\Encoder\Base32Encoder;

        $random = new Random($generator, $encoder);
        $this->assertInstanceOf('\\Rych\\Random\\Generator\\MockGenerator', $random->getGenerator());
        $this->assertInstanceOf('\\Rych\\Random\\Encoder\\Base32Encoder', $random->getEncoder());
    }

    /**
     * @test
     * @return void
     */
    public function testGenerateMethodsUsePassedInGenerator()
    {
        $generator = new \Rych\Random\Generator\MockGenerator;
        $generator->setMockString('0123456789');

        $random = new Random($generator);
        $this->assertEquals('01234567', $random->getRandomBytes(8));
        $this->assertEquals('wxyz0123', $random->getRandomString(8));
        $this->assertEquals(48, $random->getRandomInteger(0, 100));
    }

    /**
     * @test
     * @return void
     */
    public function testGenerateRandomBytesUsePassedInEncoder()
    {
        $generator = new \Rych\Random\Generator\MockGenerator;
        $generator->setMockString('0123456789');

        $encoder = new \Rych\Random\Encoder\HexEncoder;

        $random = new Random($generator, $encoder);
        $this->assertEquals('3031323334353637', $random->getRandomBytes(8));
    }

    /**
     * @test
     * @return void
     */
    public function testConstructorWithNoArgsStillBuildsValidObject()
    {
        $random = new Random;

        $this->assertInstanceOf('\\Rych\\Random\\Generator\\GeneratorInterface', $random->getGenerator());
        $this->assertInstanceOf('\\Rych\\Random\\Encoder\\EncoderInterface', $random->getEncoder());
    }

    /**
     * @test
     * @return void
     */
    public function testGetRandomStringMethodUsesCustomCharsets()
    {
        $random = new Random;

        $this->assertRegExp('/^[0-9]{10}$/', $random->getRandomString(10, '0123456789'));
        $this->assertRegExp('/^[qwerty]{10}$/', $random->getRandomString(10, 'qwerty'));
        $this->assertRegExp('/.{22}/', $random->getRandomString(21) . $random->getRandomString(1, '.Oeu'));
    }

    /**
     * @dataProvider getDataForOpenSSLGeneratesIntegerTest
     */
    public function testOpenSSLGeneratesInteger($min, $max)
    {
        if (!OpenSSLGenerator::isSupported()) {
            $this->markTestSkipped('OpenSSL is not supported on this platform.');
        }

        $random = new Random(new OpenSSLGenerator());
        $int = $random->getRandomInteger(10, $max !== null ? $max : PHP_INT_MAX);

        $this->assertTrue($int >= 10, 'The generated integer ' . $int . ' is greater (or equal) ' . $min);

        if ($max !== null) {
            $this->assertTrue($int <= $max, 'The generated integer ' . $int . ' is smaller (or equal) to ' . $max);
        }
    }

    public function getDataForOpenSSLGeneratesIntegerTest()
    {
        return array(
            array(10, null),
            array(10, 150),
            array(-10, 150),
            array(-10, null),
        );
    }
}

