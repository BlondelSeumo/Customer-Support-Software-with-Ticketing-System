<?php namespace App\Services\Reports;

use Carbon\Carbon;
use GuzzleHttp\Client;
use App\Services\Envato\EnvatoApiClient;
use Illuminate\Cache\Repository as CacheRepository;
use Illuminate\Support\Collection;

class EnvatoReport
{
    /**
     * @var Client
     */
    private $envatoClient;

    /**
     * @var CacheRepository
     */
    private $cache;

    public function __construct(EnvatoApiClient $envatoClient, CacheRepository $cache)
    {
        $this->envatoClient = $envatoClient;
        $this->cache = $cache;
    }

    /**
     * Get early earnings on envato market.
     *
     * @param array $params
     * @return array
     */
    public function getYearlyEarnings($params = [])
    {
        $year = isset($params['year']) ? (int) $params['year'] : Carbon::now()->year;

        $cacheKey = "envato.yearly.$year";

        if ($this->cache->has($cacheKey)) {
            $data = $this->cache->get($cacheKey);
        } else {
            $response = $this->envatoClient->call('market/private/user/earnings-and-sales-by-month.json', [], 'v1');
            $data = head($response);
            $this->cache->put($cacheKey, $data, Carbon::now()->addMonths(1));
        }

        $filtered = $this->filterYearlyEarningsByYear($data, $year);
        $totals   = ['sales' => 0, 'earnings' => 0];

        foreach($filtered as $item) {
            $totals['earnings'] += $item['amount'];
            $totals['sales'] += $item['sales'];
        }

        return ['yearly' => $filtered, 'totals' => $totals];
    }

    /**
     * Get monthly earnings on envato market.
     *
     * @param array $params
     * @return array
     */
    public function getMonthlyEarnings($params = [])
    {
        //prepare dates for envato API call
        $dates = $this->formatDates($params);

        //if we're fetching earnings for particular range (5th to 12th for example)
        //first check if earnings for whole month are cached already
        if (isset($dates['monthKey']) && $this->cache->has($dates['monthKey'])) {
            $response = $this->filterFormattedDataByRange($this->cache->get($dates['monthKey']), $params);
        }

        //if earnings for this time range are already cached, return cached earnings
        if ($this->cache->has($dates['rangeKey'])) {
            $response = $this->cache->get($dates['rangeKey']);
        }

        if ( ! isset($response) || ! $response) {
            $response = $this->fetchAndFormatEnvatoMonthlyEarnings($dates);
        }

        //filter by specific item
        if (isset($params['envato_item_id'])) {
            return $this->filterFormattedDataByItem($response, $params['envato_item_id']);
        }

        $response['sales'] = array_slice($response['sales'], 0, 50);

        return $response;
    }

    /**
     * Fetch, format and cache envato earnings data from Envato API.
     *
     * @param array $dates
     * @return array
     */
    private function fetchAndFormatEnvatoMonthlyEarnings($dates)
    {
        $response = ['sales' => [], 'monthly' => [], 'totals' => [], 'items' => []];

        //fetch needed data from envato API
        $r = $this->envatoClient->call('user/statement', ['from_date' => $dates['from'], 'to_date' => $dates['to']]);
        $data = $r['results'];

        if (isset($r['pagination']['pages'])) {
            $pages = $r['pagination']['pages'];
            for ($i = 2; $i <= $pages; $i++) {
                $r = $this->envatoClient->call('user/statement', ['from_date' => $dates['from'], 'to_date' => $dates['to'], 'page' => $i]);
                $data = array_merge($data, $r['results']);
            }
        }

        //if we've got no data back from envato API, bail
        if ( ! $data || empty($data)) return $response;

        //format data and prepare response
        $formatted = $this->formatStatementData($data);
        $response['monthly'] = $this->generateMonthlyEarnings($formatted);
        $response['totals']  = $this->calculateTotalsForMonth($response['monthly']);
        $response['items']   = $this->calculateItemSales($formatted, $response['totals']);
        $response['sales']   = array_values($formatted);

        $key = $dates['rangeKey'];

        //if time range is for this month, cache it for 60 minutes, otherwise for 1 month or 1 week
        //depending if time range is for whole month or just part of it
        if ( ! isset($dates['monthKey'])) {
            $cacheTime = $dates['isCurrentMonth'] ? 60 : Carbon::now()->addMonths(1);
        } else {
            $cacheTime = $dates['isCurrentMonth'] ? 60 : Carbon::now()->addDays(7);
        }

        if (count($response['sales'])) {
            $this->cache->put($key, $response, $cacheTime);
        }

        return $response;
    }

    /**
     * Filter monthly envato earnings data by given item.
     *
     * @param array $data
     * @param integer $itemId
     * @return array
     */
    private function filterFormattedDataByItem($data, $itemId)
    {
        $data['sales'] = array_values(array_filter($data['sales'], function($sale) use($itemId) {
            return $sale['item_id'] == $itemId;
        }));

        $data['monthly'] = $this->generateMonthlyEarnings($data['sales']);
        $data['totals']  = $this->calculateTotalsForMonth($data['monthly']);

        return $data;
    }

    /**
     * Filter yearly envato earnings data by given year.
     *
     * @param array $data
     * @param int $year
     * @return array
     */
    private function filterYearlyEarningsByYear($data, $year)
    {
        $filtered = [];

        foreach($data as $item) {
            $date = Carbon::parse($item['month']);

            if ($date->year == $year) {
                $filtered[$date->month] = [
                    'sales' => (int) $item['sales'],
                    'amount' => (float) $item['earnings'],
                    'month' => $date->englishMonth
                ];
            }
        }

        return $filtered;
    }

    /**
     * Filter already formatted earnings data by specific time range (from nth day to nth day)
     *
     * @param array $data
     * @param array $params
     * @return array
     */
    private function filterFormattedDataByRange($data, $params)
    {
        $response = ['sales' => [], 'monthly' => [], 'totals' => [], 'items' => []];

        $filtered = [];

        foreach($data['sales'] as $sale) {
            if ( ! isset($sale['day'])) continue;
            $saleDay = (int) $sale['day'];
            $fromDay = (int) $params['day'];
            $toDay   = (int) $params['to_day'];

            if ($saleDay >= $fromDay && $saleDay <= $toDay) {
                $filtered[] = $sale;
            }
        }

        if ( ! $filtered) return $response;

        $response['monthly'] = $this->generateMonthlyEarnings($filtered);
        $response['totals']  = $this->calculateTotalsForMonth($response['monthly']);
        $response['items']   = $this->calculateItemSales($filtered, $response['totals']);
        $response['sales']   = $filtered;

        return $response;
    }

    /**
     * Calculate sales data for separate items from all sales data.
     *
     * @param array $data   All sales data
     * @param array $totals Total number of sales and earnings
     * @return array
     */
    private function calculateItemSales($data, $totals)
    {
        $items = [];

        //calculate total number of sales and earnings for separate items
        foreach($data as $item) {
            if ( ! isset($item['item_id'])) continue;
            if (isset($items[$item['item_id']])) {
                $items[$item['item_id']]['amount'] += $item['amount'];
                $items[$item['item_id']]['sales']++;
            } else {
                $items[$item['item_id']] = ['amount' => $item['amount'], 'sales' => 1, 'name' => $item['item'], 'envato_id' => $item['item_id']];
            }

            $items[$item['item_id']]['amount'] = $this->formatFloat($items[$item['item_id']]['amount']);
        }

        //calculate what is specific items sales percentage of total sales
        foreach($items as $k => $item) {
            $items[$k]['percentage'] = round($item['amount'] / $totals['earnings'] * 100);
        }

        //sort, highest percentage first
        uasort($items, function($a, $b) {
            if ($a['percentage'] == $b['percentage']) {
                return 0;
            }

            return ($a['percentage'] > $b['percentage']) ? -1 : 1;
        });

        return array_values($items);
    }

    /**
     * Generate total earnings and number of sales for month.
     *
     * @param $earnings
     * @return array
     */
    private function calculateTotalsForMonth($data)
    {
        $earnings = array_reduce($data, function($carry, $item) {
            $carry += $item['amount'];
            return $carry;
        });

        $sales = array_reduce($data, function($carry, $item) {
            $carry += $item['sales'];
            return $carry;
        });

        return ['sales' => $sales, 'earnings' => ceil($earnings)];
    }

    /**
     * Make to and from dates from given params or return default ones.
     *
     * @param array $params
     * @return array
     */
    private function formatDates($params = [])
    {
        $dates = [];

        $from = Carbon::now()->addMonth(-1)->firstOfMonth();

        if (isset($params['month'])) {
            $from->month = (int) $params['month'];
        }

        if (isset($params['year'])) {
            $from->year = (int) $params['year'];
        }

        if (isset($params['day'])) {
            $from->day = (int) $params['day'];
        }

        $to = Carbon::createFromTimestamp($from->timestamp)->lastOfMonth();

        if (isset($params['to_day']) && $params['to_day']) {
            $to->day = (int) $params['to_day'];
        }

        //if time range is for current month
        $dates['isCurrentMonth'] = $to->month == Carbon::now()->month;

        //cache key for given specific date range
        $rangeKey = 'envato.'.((string)$from->timestamp.(string)$to->timestamp);

        //cache key for whole month (1st to 31st)
        $firstOfMonth = Carbon::createFromTimestamp($from->timestamp)->firstOfMonth();
        $lastOfMonth  = Carbon::createFromTimestamp($from->timestamp)->lastOfMonth();
        $monthKey ='envato.'.((string)$firstOfMonth->timestamp.(string)$lastOfMonth->timestamp);

        //from/to date strings
        $dates['from']     = $from->toDateString();
        $dates['to']       = $to->toDateString();
        $dates['rangeKey'] = $rangeKey;

        if ($monthKey !== $rangeKey) {
            $dates['monthKey'] = $monthKey;
        }

        return $dates;
    }

    /**
     * Format envato statement data for displaying in charts.
     *
     * @param array $data
     * @return array
     */
    private function formatStatementData($data)
    {
        // filter out withdrawals
        $data = collect($data)
            ->filter(function($item) {
                return $item['order_id'];
            });

        list($refunds, $sales) = collect($data)
            ->partition(function($item) {
                return \Str::startsWith($item['detail'], ['Refund', 'Reversal of']);
            });

        $formatted = $sales->map(function($item) {
                $date = Carbon::parse($item['date']);
                $nameParts = explode('(', $item['detail']);
                return [
                    'date' => $date->toDateString(),
                    'day' => $date->day,
                    // multiple items can be purchased in same order but we need
                    // to group sale/fee records by item and not by order id
                    'sale_id'  => $item['order_id'] + $item['item_id'],
                    'order_id' => $item['order_id'],
                    'item_id'  => (int) $item['item_id'],
                    'amount'   => (float) abs($item['amount']),
                    'price'   => $item['price'],
                    'type'     => $item['type'] === 'Sale' ? 'sale' : 'fee',
                    // MTDb - Ultimate Movie&TV Database (6 months included support)
                    'item'  => trim($nameParts[0]),
                    'included_support' => \Str::contains($item['detail'], 'included support'),
                ];
            })->groupBy('sale_id')
            ->map(function(Collection $saleGroup) {
                list($fees, $sales) = $saleGroup->partition(function($item) {
                    return $item['type'] === 'fee';
                });

                $totalFee = $fees->sum('amount');
                $totalEarnings = $sales->sum('price');
                $itemSales = $saleGroup->filter(function($item) {
                    return $item['type'] === 'sale' && $item['included_support'] === false;
                });

                $uniqueItem = $itemSales->first();
                $uniqueItem['amount'] = $totalEarnings - $totalFee;
                $uniqueItem['count'] = $itemSales->count();
                return $uniqueItem;
            });

        return $formatted->toArray();
    }

    /**
     * Generate monthly earnings array ([01 => 450, 02 => 899...])
     *
     * @param array $sales
     * @return array
     */
    private function generateMonthlyEarnings($sales)
    {
        if (empty($sales)) return $sales;

        $monthly = [];

        foreach($sales as $k => $item) {
            if ( ! isset($item['day'])) continue;
            if (isset($monthly[$item['day']])) {
                $monthly[$item['day']]['amount'] += $item['amount'];
                $monthly[$item['day']]['sales'] ++;
            } else {
                $monthly[$item['day']] = [
                    'amount' => $item['amount'],
                    'sales'  => 1,
                ];
            }

            $monthly[$item['day']]['amount'] = $this->formatFloat($monthly[$item['day']]['amount']);
        }

        $daysInMonth = Carbon::parse(head($sales)['date'])->daysInMonth;

        //if we don't have earnings for all days of month,
        //fill the rest of days with 0 earnings
        if (count($monthly) < $daysInMonth) {
            foreach(array_fill(1, $daysInMonth, 0) as $k => $val) {
                if ( ! isset($monthly[$k])) {
                    $monthly[$k] = ['amount' => 0, 'sales' => 0];
                }
            }
        }

        ksort($monthly);

        return $monthly;
    }

    /**
     * Limit specified float to 2 decimal points.
     *
     * @param float $float
     * @return float
     */
    private function formatFloat($float)
    {
        return round($float, 2);
    }
}
