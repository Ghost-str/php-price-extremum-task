<?php  declare(strict_types=1);
namespace Phptesttask;

class PriceHistoryStorage {

    private $intervals = [];
    private $priceData = [];

    public function __construct($intervals) {
        foreach ($intervals as $interval) {
            $this->intervals[$interval] = [
                'min_price'=> PHP_FLOAT_MAX,
                'max_price'=> PHP_FLOAT_MIN,
                'last_price_index' => -1
            ];
        };
    }

    public function addPrice(int|float $price, int $timestamp) {
        $this->priceData[] = ['price' => $price, 'timestamp' => $timestamp];

        foreach ($this->intervals as &$interval) {
            $interval['min_price'] = min($price, $interval['min_price']);
            $interval['max_price'] = max($price, $interval['max_price']);
        }
    }

    public function getMinPrice(int $interval): float|null {
        return $this->price_minimax($interval, 'min');
    }
    
   
    public function getMaxPrice(int $interval): float|null {
        return $this->price_minimax($interval, 'max');
    }


    private function price_minimax(int $interval, string $type):  float|null {
        $this->check_interval($interval);
        $this->check_lats_index($interval);

        if ($this->is_data_range_empty($interval)) {
            return null;
        }

        return  $this->intervals[$interval][$type."_price"];
    }


    private function check_lats_index(int $interval, $is_reacalculate = false): void {

        $current_time = time();
        $value_max_time = $current_time - $interval;
        
        $inteval_state = &$this->intervals[$interval];
        $data_item = $this->priceData[$inteval_state['last_price_index']+1];
        
        if ($value_max_time <= $data_item['timestamp'] || $this->is_data_range_empty($interval)) {
            if ($is_reacalculate) {
                    $this->recalculate_interval($interval);
            }
            return;
        }

        $inteval_state['last_price_index']++;
        $reacalculate = $is_reacalculate || !($data_item['price'] === $inteval_state['min_price'] || $data_item['price'] === $inteval_state['max_price']);
        
        $this->check_lats_index($interval,  $reacalculate);
    }


    private function recalculate_interval(int $interval) {
        $inteval_state = &$this->intervals[$interval];
        $inteval_state['min_price'] = PHP_FLOAT_MAX;
        $inteval_state['max_price'] = PHP_FLOAT_MIN;

        $recalc_range =range($this->get_first_data_index(), $inteval_state['last_price_index']+1);

        foreach ($recalc_range as $index) {
            $data_state = $this->priceData[$index];
            $inteval_state['min_price'] = min($inteval_state['min_price'], $data_state['price']);
            $inteval_state['max_price'] = max($inteval_state['max_price'], $data_state['price']);
        }
    }

    private function get_first_data_index():int {
        return count($this->priceData)-1;
    }



    private function check_interval(int $interval) {
        if (!isset( $this->intervals[$interval])) {
            throw new \InvalidArgumentException("Interval '$interval' did not exist");
        }
    }

    private function is_data_range_empty(int $interval): bool {
       return $this->intervals[$interval]['last_price_index'] === $this->get_first_data_index();
    }
}