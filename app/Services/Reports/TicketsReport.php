<?php namespace App\Services\Reports;

use App\Ticket;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Common\Auth\Actions\PaginateUsers;
use Common\Settings\Settings;
use DB;
use Illuminate\Cache\Repository as CacheRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class TicketsReport
{
    /**
     * @var Ticket
     */
    private $ticket;

    /**
     * @var CacheRepository
     */
    private $cache;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @var CarbonPeriod
     */
    private $range;

    /**
     * @var array
     */
    private $report;

    /**
     * @var int
     */
    private $chunkSize = 200;

    /**
     * @var Collection
     */
    private $agents;

    /**
     * @var Collection
     */
    private $agentsIds;

    public function __construct(CacheRepository $cache, Ticket $ticket, Settings $settings)
    {
        $this->cache = $cache;
        $this->ticket = $ticket;
        $this->settings = $settings;
    }

    /**
     * Generate a full report on tickets for given time period.
     *
     * @param array $params
     * @return array
     */
    public function generateFull($params)
    {
        $this->range  = $this->createTimeRange($params);
        $this->report = $this->getEmptyReport();
        $cacheKey     = $this->makeCacheKey();

        if ($this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        $this->generateReportForTimeRange();

        $this->cache->put($cacheKey, $this->report, (int) $this->settings->get('cache.report_minutes', 60));

        return $this->report;
    }

    /**
     * Generate report for currently set time range.
     */
    private function generateReportForTimeRange()
    {
        DB::table('tickets')
            ->select('id', 'user_id', 'created_at', 'closed_at', 'closed_by')
            ->whereBetween(
                'tickets.created_at',
                [$this->range->getStartDate(), $this->range->getEndDate()]
            )
            ->orderBy('created_at', 'desc')
            ->chunk($this->chunkSize, function($tickets) {
                $tickets = $this->prepareTickets($tickets);
                $this->generateReportFromTickets($tickets);
            });

        $this->calcAveragesAndPercentages();
    }

    private function createTimeRange(array $params): CarbonPeriod
    {
        $now = Carbon::now();

        $fromYear  = isset($params['from_year']) ? (int) $params['from_year'] : $now->year;
        $fromMonth = isset($params['from_month']) ? (int) $params['from_month'] : $now->month;
        $fromDay   = isset($params['from_day']) ? (int) $params['from_day'] : $now->day;
        $from      = Carbon::create($fromYear, $fromMonth, $fromDay, 0, 0, 0);

        $to = Carbon::create($fromYear, $fromMonth, $fromDay, 0, 0, 0)->addMonth();
        if (isset($params['to_year'])) $to->year = (int) $params['to_year'];
        if (isset($params['to_month'])) $to->month = (int) $params['to_month'];
        if (isset($params['to_day'])) $to->day = (int) $params['to_day'];

        return CarbonPeriod::create($from, $to);
    }

    /**
     * Create cache key for current time range timestamps.
     *
     * @return string
     */
    private function makeCacheKey()
    {
        return (string) $this->range->getStartDate()->timestamp .
        (string) $this->range->getEndDate()->timestamp;
    }

    /**
     * Generate report from given tickets. It will merge this report
     * with any previously generated reports so generation can be chunked.
     *
     * @param Collection $tickets
     */
    private function generateReportFromTickets(Collection $tickets)
    {
        $this->report['newTickets'] += $tickets->count();

        $this->calcNumberOfSolvedTickets($tickets);

        $this->calcNumberOfUnsolvedTickets($tickets);

        $this->calcFirstResponseTimes($tickets);

        $this->calcAvgResponseTime($tickets);

        $this->calcDailyTicketCounts($tickets);

        $this->calcHourlyTicketCounts($tickets);

        $this->generateTagsReport($tickets);

        $this->generateAgentsReport($tickets);
    }

    /**
     * Generate report on agent performance.
     *
     * @param Collection $tickets
     */
    private function generateAgentsReport($tickets)
    {
        if ( ! $this->report['agents']) {
            $this->report['agents'] = [];

            $this->agents = collect(app(PaginateUsers::class)->execute([
                'permission' => 'tickets.update',
                'perPage' => 20
            ])->items());

            $this->agentsIds = $this->agents->pluck('id');
        }

        $tickets->each(function($ticket) {
            //count number of replies each agent made
            $ticket->replies->each(function($reply) {
                if ($this->agentsIds->contains($reply->user_id)) {
                    $this->incrementAgentStats($reply->user_id, 'replies');
                }
            });

            //count number of tickets each agent closed
            if ($this->agentsIds->contains($ticket->closed_by)) {
                $this->incrementAgentStats($ticket->closed_by, 'ticketsSolved');
            }
        });
    }

    /**
     * Calculate average response time of given agent.
     *
     * @param integer $userId
     * @return integer
     */
    private function calcAvgAgentResponseTime($userId)
    {
        $hours = [];

        foreach($this->report['avgResponseTime'] as $item) {
            if ($item['user_id'] == $userId) {
                $hours[] = $item['hours'];
            }
        }

        if (empty($hours)) return 0;

        return round(array_sum($hours) / count($hours), 1);
    }

    /**
     * Increment given agent statistic in agents performance report.
     *
     * @param integer $agentId
     * @param string $statName
     */
    private function incrementAgentStats($agentId, $statName)
    {
        if ( ! isset($this->report['agents'][$agentId])) {
            $this->report['agents'][$agentId] = ['replies' => 0, 'ticketsSolved' => 0, 'id' => $agentId];
        }

        $this->report['agents'][$agentId][$statName]++;
    }

    /**
     * Calculate amount of tickets solved within time range.
     *
     * @param Collection $tickets
     */
    private function calcNumberOfSolvedTickets($tickets)
    {
        $this->report['solvedTickets'] += $tickets->filter(function ($ticket) {
            return $ticket->tags->contains('name', 'closed') &&
                $ticket->closed_at->between($this->range->getStartDate(), $this->range->getEndDate());
        })->count();
    }

    /**
     * Calculate number of unsolved tickets within time range.
     *
     * @param Collection $tickets
     */
    private function calcNumberOfUnsolvedTickets($tickets)
    {
        $this->report['openTickets'] += $tickets->filter(function($ticket) {
            return $ticket->tags->contains('name', 'open');
        })->count();
    }

    /**
     * Calculate average time to first reply.
     *
     * @param Collection $tickets
     */
    private function calcFirstResponseTimes($tickets)
    {
        $firstReplyTimes = $tickets->map(function ($ticket) {
            $creatorId = $ticket->user_id;

            //latest replies will be first in array
            $firstAgentReply = $ticket->replies->last(function ($reply) use ($creatorId) {
                return $reply->user_id != $creatorId;
            });

            return $firstAgentReply ? $ticket->created_at->diffInHours($firstAgentReply->created_at) : null;
        })->filter();

        //we'll calculate average time later, for now just merge arrays
        //of first reply times, so report generation can be chunked
        $this->report['firstResponseTimes']['average'] = array_merge(
            $this->report['firstResponseTimes']['average'],
            $firstReplyTimes->toArray()
        );

        $firstReplyTimes->groupBy(function($item) {
            return $item;
        })->each(function($occurrences, $hours) {
            if ($hours <= 1) {
                $this->report['firstResponseTimes']['breakdown']['0-1']['count'] += count($occurrences);
            }

            if ($hours <= 8) {
                $this->report['firstResponseTimes']['breakdown']['0-8']['count'] += count($occurrences);
            }

            if ($hours <= 24 && $hours > 8) {
                $this->report['firstResponseTimes']['breakdown']['8-24']['count'] += count($occurrences);
            }

            if ($hours > 24) {
                $this->report['firstResponseTimes']['breakdown']['>24']['count'] += count($occurrences);
            }
        });
    }

    /**
     * Calculate overall average time to agent response.
     *
     * @param Collection $tickets
     */
    private function calcAvgResponseTime($tickets)
    {
        $avgResponseTimes = $tickets->map(function ($ticket) {
            $hours = collect([]);

            //latest replies are first in array
            foreach ($ticket->replies->values() as $k => $reply) {
                if ($reply->user_id != $ticket->user_id && $previous = $ticket->replies->get($k + 1)) {
                    $hours->push([
                        'hours'   => $previous->created_at->diffInHours($reply->created_at),
                        'user_id' => $reply->user_id
                    ]);
                }
            }

            return $hours;
        })->flatten(1)->filter(function($item) {
            return $item['hours'] > 0;
        });

        $this->report['avgResponseTime'] = array_merge(
            $this->report['avgResponseTime'],
            $avgResponseTimes->toArray()
        );
    }

    /**
     * Generate report for ticket tags.
     *
     * @param Collection $tickets
     */
    private function generateTagsReport(Collection $tickets)
    {
        $tickets->flatMap(function($ticket) {
            return $ticket->tags;
        })->filter(function($tag) {
            return $tag->type !== 'status';
        })->groupBy('name')
        ->map(function($values) {
            $name = $values[0]->display_name ? $values[0]->display_name : $values[0]->name;

            return [
                'count' => $values->count(),
                'name'  => Str::limit($name, 18),
            ];
        })->each(function($tag) {
            //if statistics for this tag already exist, we'll need to sum them
            if (isset($this->report['tags'][$tag['name']])) {
                $this->report['tags'][$tag['name']]['count'] += $tag['count'];
            } else {
                $this->report['tags'][$tag['name']] = $tag;
            }
        });
    }

    /**
     * Calculate daily ticket counts (over multiple or single month).
     *
     * @param Collection $tickets
     */
    private function calcDailyTicketCounts($tickets)
    {
        $tickets->map(function($ticket) {
            return ['monthDay' => (string)$ticket->created_at->month.'.'.(string)$ticket->created_at->day];
        })
        ->groupBy('monthDay', true)
        ->each(function($val, $monthDay) {
            $this->report['dailyCounts'][$monthDay]['count'] += $val->count();
        })->toArray();
    }

    /**
     * Calculate hourly ticket counts (2 hour intervals)
     *
     * @param Collection $tickets
     */
    private function calcHourlyTicketCounts($tickets)
    {
        $data = $tickets->map(function($ticket) {

            //first day in carbon is sunday, we need it to be monday
            $day = $ticket->created_at->dayOfWeek > 0 ? $ticket->created_at->dayOfWeek -1 : 6;

            return ['day' => $day, 'hour' => $ticket->created_at->hour + 1];
        });

        $data->each(function($val) {

            //round hour to nearest number divisible by 2
            $toHour   = $val['hour'] == 0 ? 2 : (int) round($val['hour'] / 2) * 2;

            //we are displaying ticket counts in 2 hour increments
            $fromHour = (int) $toHour - 2;

            //make an array key from fromHour and toHour (example: 12-14)
            $key     = $fromHour.'-'.$toHour;

            //increment ticket count in time interval
            $this->report['hourlyCounts']['data'][$val['day']]['counts'][$key]++;
        });

        //find the highest amount of tickets in any 2 hour period
        $this->report['hourlyCounts']['max'] = max(array_map(function($dayStats) {
            return max($dayStats['counts']);
        }, $this->report['hourlyCounts']['data']));
    }

    /**
     * Calculate all averages and percentages for report. Need to
     * do it here because report is generated in 200 ticket chunks.
     */
    private function calcAveragesAndPercentages()
    {
        //add agent emails to their statistics and calculate their average response time
        if ($this->agents) {
            foreach($this->agents as $user) {
                if ( ! isset($this->report['agents'][$user->id])) continue;

                $this->report['agents'][$user->id]['email'] = $user->email;
                $this->report['agents'][$user->id]['avgResponseTime'] = $this->calcAvgAgentResponseTime($user->id);
            }

            //sort agents report by number of replies
            usort($this->report['agents'], function($a, $b) {
                return $b['replies'] - $a['replies'];
            });

            $this->report['agents'] = array_values($this->report['agents']);
        }

        //calculate what percentage of total new tickets is each tag
        if ($this->report['tags']) {
            foreach($this->report['tags'] as $tagName => $stats) {
                $this->report['tags'][$tagName]['percentage'] = round(($stats['count'] / $this->report['newTickets']) * 100, 1);
            }
        }

        //calculate average overall response time
        if ($count = count($this->report['avgResponseTime'])) {
            $sum = array_sum(array_map(function($item){
                return $item['hours'];
            }, $this->report['avgResponseTime']));

            $allAverage = round($sum / $count, 1);
        } else {
            $allAverage = 0;
        }

        $this->report['avgResponseTime'] = $allAverage;

        //calculate average first response time
        if ($firstAvgCount = count($this->report['firstResponseTimes']['average'])) {
            $firstAvg = round(array_sum($this->report['firstResponseTimes']['average']) / $firstAvgCount, 1);
        } else {
            $firstAvg = 0;
        }

        $this->report['firstResponseTimes']['average'] = $firstAvg;

        //calculate first response time percentages for specific hours
        $total = array_reduce($this->report['firstResponseTimes']['breakdown'], function($carry, $item) {
            return $carry + $item['count'];
        });

        foreach($this->report['firstResponseTimes']['breakdown'] as $k => $stats) {
            if ($total) {
                $percentage = round(($stats['count'] / $total) * 100, 1);
            } else {
                $percentage = 0;
            }

            $this->report['firstResponseTimes']['breakdown'][$k]['percentage'] = $percentage;
        }
    }

    /**
     * Load needed relationships and format tickets for report generation.
     *
     * @param Collection $tickets
     * @return Collection
     */
    private function prepareTickets($tickets)
    {
        $tickets = collect($tickets);

        //get array of all ticket ids
        $ids = $tickets->pluck('id');

        //load tags of all given tickets
        $tags = DB::table('tags')
            ->select('tags.name', 'tags.id', 'tags.type', 'tags.display_name', 'taggables.taggable_id as ticket_id')
            ->leftJoin('taggables', 'taggables.tag_id', '=', 'tags.id')
            ->where('taggables.taggable_type', 'App\Ticket')
            ->whereIn('taggables.taggable_id', $ids)
            ->get();

        //build tags dictionary
        $tagsDict = [];
        foreach ($tags as $tag) {
            $tagsDict[$tag->ticket_id][] = $tag;
        }

        //load replies of all given tickets
        $replies = DB::table('replies')
            ->select('id', 'ticket_id', 'user_id', 'created_at')
            ->whereIn('ticket_id', $ids)
            ->orderBy('created_at', 'desc')
            ->get();

        //build replies dictionary
        $repliesDict = [];
        foreach ($replies as $reply) {
            $reply->created_at = Carbon::parse($reply->created_at);
            $repliesDict[$reply->ticket_id][] = $reply;
        }

        foreach($tickets as $k => $ticket) {
            //initiate carbon instances
            $tickets[$k]->closed_at = Carbon::parse($ticket->closed_at);
            $tickets[$k]->created_at = Carbon::parse($ticket->created_at);

            //assign tags and replies to tickets they belong to
            $tickets[$k]->replies = collect(isset($repliesDict[$ticket->id]) ? $repliesDict[$ticket->id] : []);
            $tickets[$k]->tags = collect(isset($tagsDict[$ticket->id]) ? $tagsDict[$ticket->id] : []);
        }

        return $tickets;
    }

    /**
     * Set size of tickets database query chunk.
     *
     * @param int $size
     */
    public function setChunkSize($size)
    {
        $this->chunkSize = $size;
    }

    /**
     * Return report data format template.
     *
     * @return array
     */
    private function getEmptyReport()
    {
        $template = [
            'newTickets' => 0,
            'solvedTickets' => 0,
            'openTickets' => 0,
            'dailyCounts' => [],
            'tags'   => null,
            'agents' => null,
            'avgResponseTime' => [],
            'firstResponseTimes' => [
                'average'   => [],
                'breakdown' => [
                    '0-1'  => ['count' => 0, 'percentage' => 0],
                    '0-8'  => ['count' => 0, 'percentage' => 0],
                    '8-24' => ['count' => 0, 'percentage' => 0],
                    '>24'  => ['count' => 0, 'percentage' => 0],
                ]
            ],
            'hourlyCounts' => [
                'max'  => 0,
                'data' => [],
            ]
        ];

        $template['hourlyCounts']['data'] = array_fill(0, 7, ['day' => '', 'counts' => [
            '0-2' => 0, '2-4' => 0, '4-6' => 0, '6-8' => 0, '8-10' => 0, '10-12' => 0,
            '12-14' => 0, '14-16' => 0, '16-18' => 0, '18-20' => 0, '20-22' => 0, '22-24' => 0
        ]]);

        foreach ($this->range as $date) {
            $key = (string)$date->month.'.'.(string)$date->day;
            $template['dailyCounts'][$key] = [
                'label' => $date->format('M, d'),
                'count' => 0
            ];
        }

        $sunday = Carbon::now()->endOfWeek();

        foreach($template['hourlyCounts']['data'] as $k => $v) {
            $template['hourlyCounts']['data'][$k]['day'] = Str::limit(
                $sunday->addDays(1)->formatLocalized('%a'), 5
            );
        }

        return $template;
    }
}
