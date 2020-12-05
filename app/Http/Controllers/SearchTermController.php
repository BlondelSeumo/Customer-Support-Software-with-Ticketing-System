<?php

namespace App\Http\Controllers;

use App\SearchTerm;
use Axisofstevil\StopWords\Filter;
use Common\Core\BaseController;
use Illuminate\Http\Request;

class SearchTermController extends BaseController
{
    /**
     * @var SearchTerm
     */
    private $searchTerm;

    /**
     * @var Request
     */
    private $request;

    /**
     * @param SearchTerm $searchTerm
     * @param Request $request
     */
    public function __construct(SearchTerm $searchTerm, Request $request)
    {
        $this->searchTerm = $searchTerm;
        $this->request = $request;
    }

    public function store()
    {
        $normalizedTerm = slugify(
            (new Filter())->cleanText($this->request->get('term'))
        );

        $term = $this->searchTerm->create([
            'term' => $this->request->get('term'),
            'normalized_term' => $normalizedTerm,
            'result_count' => $this->request->get('resultCount'),
            'created_ticket' => $this->request->get('createdTicket'),
            'clicked_article' => $this->request->get('clickedArticle'),
            'category_id' => $this->request->get('categoryId'),
            'source' => $this->request->get('source'),
            'user_id' => $this->request->get('userId'),
            'page' => $this->request->get('page'),
        ]);

        return $this->success(['term' => $term]);
    }
}
