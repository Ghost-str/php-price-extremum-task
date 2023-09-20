<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use Phptesttask\PriceHistoryStorage;
use SlopeIt\ClockMock\ClockMock;

final class PriceHistoryStorageTest extends TestCase
{

    public function testCannotGetMaxPriceOfNonExistingInterval(): void
    {
        $this->expectException(InvalidArgumentException::class);
       
        $storage = new PriceHistoryStorage([300]);


        $storage->getMaxPrice(200);
        
    }


    public function testCannotGetMinPriceOfNonExistingInterval(): void
    {
        $this->expectException(InvalidArgumentException::class);
       
        $storage = new PriceHistoryStorage([300]);


        $storage->getMinPrice(200);
        
    }

    public function testMinMaxPriceWithoutDataValues(): void
    {
       
        $storage = new PriceHistoryStorage([300]);


       $this->assertSame(null, $storage->getMaxPrice(300)) ;
       $this->assertSame(null, $storage->getMinPrice(300)) ;
        
    }

    public function testMinMaxPriceWithDataValues(): void 
    {

        ClockMock::freeze(new \DateTime('2023-09-17 00:05:00'));
        $storage = new PriceHistoryStorage([300]);
        
        foreach (range(0,10) as $price) {
            $storage->addPrice($price, time()+$price-300);
        }


        $this->assertSame(10.0, $storage->getMaxPrice(300));
        $this->assertSame(0.0, $storage->getMinPrice(300));

        ClockMock::freeze(new \DateTime('2023-09-17 00:05:05'));

        $this->assertSame(10.0, $storage->getMaxPrice(300));
        $this->assertSame(5.0, $storage->getMinPrice(300));

        ClockMock::freeze(new \DateTime('2023-09-17 00:05:10'));

        $this->assertSame(10.0, $storage->getMaxPrice(300));
        $this->assertSame(10.0, $storage->getMinPrice(300));

        ClockMock::freeze(new \DateTime('2023-09-17 00:05:11'));

        $this->assertSame(null, $storage->getMaxPrice(300));
        $this->assertSame(null, $storage->getMinPrice(300));

        ClockMock::reset();

    }


}
