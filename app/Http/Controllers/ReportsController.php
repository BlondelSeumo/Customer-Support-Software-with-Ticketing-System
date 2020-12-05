<?php namespace App\Http\Controllers;

use App\Services\Reports\HelpCenterReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\Reports\TicketsReport;
use App\Services\Reports\EnvatoReport;
use Common\Core\BaseController;

class ReportsController extends BaseController
{
    /**
     * EnvatoReports service instance.
     *
     * @var EnvatoReport
     */
    private $envato;

    /**
     * TicketReports service instance.
     *
     * @var TicketsReport
     */
    private $ticketReports;

    /**
     * Laravel request instance.
     *
     * @var Request
     */
    private $request;

    /**
     * ReportsController constructor.
     *
     * @param Request $request
     * @param EnvatoReport $envato
     * @param TicketsReport $ticketReports
     */
    public function __construct(Request $request, EnvatoReport $envato, TicketsReport $ticketReports)
    {
        $this->envato = $envato;
        $this->request = $request;
        $this->ticketReports = $ticketReports;
    }

    public function helpCenterReport()
    {
        $this->authorize('index', 'ReportPolicy');

        $report = app(HelpCenterReport::class)->generate();

        return $this->success(['report' => $report]);
    }

    public function userSearches($userId)
    {
        $this->authorize('index', 'ReportPolicy');

        $report = app(HelpCenterReport::class)
            ->generateSearchReport(['user_id' => $userId], 'last_seen', 'desc');

        return $this->success(['report' => $report]);
    }

    /**
     * @return JsonResponse
     */
    public function envatoEarnings()
    {
        $this->authorize('index', 'ReportPolicy');

        if ($this->request->get('yearly') == 'true') {
            $data = $this->envato->getYearlyEarnings($this->request->all());
        } else {
            $data = $this->envato->getMonthlyEarnings($this->request->all());
        }

        return $this->success(['data' => $data]);
    }

    public function generateTicketsReport()
    {
        $this->authorize('index', 'ReportPolicy');

        $data = $this->ticketReports->generateFull($this->request->all());
        return $this->success(['data' => $data]);
    }
}
