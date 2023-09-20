<?php  declare(strict_types=1);
namespace Phptesttask;

/**
 * PriceHistoryStorage
 * Class for calculation price extremums for data frame
 */
class PriceHistoryStorage {

    private $intervals = [];
    private $priceData = [];
    
    /**
     * __construct
     *
     * @param  array $intervals list of datime framees in seconds 
     * example `new PriceHistoryStorage([300, 900, 3600, 14400,86400]);`
     * @return void
     */
    public function __construct(array $intervals) {
        foreach ($intervals as $interval) {
            $this->intervals[$interval] = [
                'min_price'=> PHP_FLOAT_MAX,
                'max_price'=> PHP_FLOAT_MIN,
                'last_price_index' => -1
            ];
        };
    }
    
    /**
     * addPrice
     * 
     * it add price item to store
     *
     * @param  int|float $price
     * @param  int       $timestamp
     * @return void
     */
    public function addPrice(int|float $price, int $timestamp) {
        $this->priceData[] = ['price' => $price, 'timestamp' => $timestamp];

        foreach ($this->intervals as &$interval) {
            $interval['min_price'] = min($price, $interval['min_price']);
            $interval['max_price'] = max($price, $interval['max_price']);
        }
    }
    
    /**
     * getMinPrice
     * 
     * it return min price for data frame
     *
     * @param  int $interval
     * @return float|null
     */
    public function getMinPrice(int $interval): float|null {
        return $this->priceMinimax($interval, 'min');
    }
    
   
     /**
     * getMaxPrice
     * 
     * it return max price for data frame
     *
     * @param  int $interval
     * @return float|null
     */
    public function getMaxPrice(int $interval): float|null {
        return $this->priceMinimax($interval, 'max');
    }

        
    /**
     * priceMinimax
     * 
     * it return max|min price for data frame
     *
     * @param  int $interval
     * @param  string $type min | max
     * @return float|null
     */
    private function priceMinimax(int $interval, string $type):  float|null {
        $this->checkDataFrame($interval);
        $this->checkLastValue($interval);

        if ($this->isDataRangeEmpty($interval)) {
            return null;
        }

        return  $this->intervals[$interval][$type."_price"];
    }

    
    /**
     * checkLastValue
     * 
     * it check cursor for last element in data list for current data frame
     *
     * @param  int $interval
     * @param  bool $is_reacalculate
     * @return void
     */
    private function checkLastValue(int $interval, $is_recalculate = false): void {

        $current_time = time();
        $value_max_time = $current_time - $interval;
        
        $interval_state = &$this->intervals[$interval];
        $data_item = $this->priceData[$interval_state['last_price_index']+1];
        
        if ($value_max_time <= $data_item['timestamp'] || $this->isDataRangeEmpty($interval)) {
            if ($is_recalculate) {
                    $this->recalculateInterval($interval);
            }
            return;
        }

        $interval_state['last_price_index']++;
        $recalculate = $is_recalculate || !($data_item['price'] === $interval_state['min_price'] || $data_item['price'] === $interval_state['max_price']);
        
        $this->checkLastValue($interval,  $recalculate);
    }

    
    /**
     * recalculateInterval
     * 
     * it reacalculete interval starting from the last element antil cursor
     *
     * @param  mixed $interval
     * @return void
     */
    private function recalculateInterval(int $interval) {
        $inteval_state = &$this->intervals[$interval];
        $inteval_state['min_price'] = PHP_FLOAT_MAX;
        $inteval_state['max_price'] = PHP_FLOAT_MIN;

        $recalc_range =range($this->getLastValueIndex(), $inteval_state['last_price_index']+1);

        foreach ($recalc_range as $index) {
            $data_state = $this->priceData[$index];
            $inteval_state['min_price'] = min($inteval_state['min_price'], $data_state['price']);
            $inteval_state['max_price'] = max($inteval_state['max_price'], $data_state['price']);
        }
    }
    
    /**
     * getLastValueIndex
     *
     * @return int
     */
    private function getLastValueIndex():int {
        return count($this->priceData)-1;
    }


    
    /**
     * checkDataFrame
     * 
     * check if data frame exist in storage
     *
     * @param  int $interval
     * @return void
     */
    private function checkDataFrame(int $interval) {
        if (!isset( $this->intervals[$interval])) {
            throw new \InvalidArgumentException("Interval '$interval' did not exist in PriceHistoryStorage");
        }
    }

    
    /**
     * isDataRangeEmpty
     * 
     * check if is there are elements bitween last element and current cursor 
     *
     * @param  mixed $interval
     * @return bool
     */
    private function isDataRangeEmpty(int $interval): bool {
       return $this->intervals[$interval]['last_price_index'] === $this->getLastValueIndex();
    }
}