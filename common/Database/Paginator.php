<?php namespace Common\Database;

use Cache;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class Paginator
{
    /**
     * @var Builder
     */
    private $query;

    /**
     * @var Model
     */
    private $model;

    /**
     * @var string
     */
    private $defaultOrderColumn = 'updated_at';

    /**
     * @var string
     */
    private $defaultOrderDirection = 'desc';

    /**
     * @var int
     */
    public $defaultPerPage = 15;

    /**
     * @var string
     */
    public $searchColumn = 'name';

    /**
     * @var array
     */
    public $filterColumns = [];

    /**
     * @var Closure
     */
    public $searchCallback;

    /**
     * @var array
     */
    private $params;

    /**
     * @var bool
     */
    public $dontSort;

    /**
     * @var string|null
     */
    private $countCacheKey;

    /**
     * @param Model|Builder $model
     * @param array $params
     * @param string|null $countCacheKey
     */
    public function __construct($model, $params, $countCacheKey = null)
    {
        $this->model = $model;
        $this->params = $this->toCamelCase($params);
        $this->query = $model->newQuery();
        $this->countCacheKey = $countCacheKey;
    }

    /**
     * @return LengthAwarePaginator
     */
    public function paginate()
    {
        $with = array_filter(explode(',', $this->param('with', '')));
        $withCount = array_filter(explode(',', $this->param('withCount', '')));
        $searchTerm = $this->param('query');
        $order = $this->getOrder();
        $perPage = $this->param('perPage', $this->defaultPerPage);
        $page = (int) $this->param('page', 1);

        // load specified relations and counts
        if ( ! empty($with)) $this->query->with($with);
        if ( ! empty($withCount)) $this->query->withCount($withCount);

        // search
        if ($searchTerm) {
            if ($this->searchCallback) {
                call_user_func($this->searchCallback, $this->query, $searchTerm);
            } else {
                $this->query->where($this->searchColumn, 'like', "$searchTerm%");
            }
        }

        $this->applyFilters();

        // order
       if ( ! $this->dontSort) {
           $this->query->orderBy($order['col'], $order['dir']);
       }

       $count = null;
       if ($this->countCacheKey) {
           $count = Cache::get($this->countCacheKey);
       }
       if (is_null($count)) {
           $count = $this->query()->count();
       }

        // paginate
        return new LengthAwarePaginator(
            with(clone $this->query)->skip(($page - 1) * $perPage)->take($perPage)->get(),
            $count,
            $perPage,
            $page
        );
    }

    public function param(string $name, $default = null)
    {
        return Arr::get($this->params, Str::camel($name)) ?: $default;
    }

    public function setParam(string $name, $value)
    {
        $this->params[$name] = $value;
    }

    /**
     * @return Builder
     */
    public function query() {
        return $this->query;
    }

    /**
     * Load specified relation counts with paginator items.
     *
     * @param mixed $relations
     * @return $this
     */
    public function withCount($relations)
    {
        $this->query->withCount($relations);
        return $this;
    }

    /**
     * Load specified relations of paginated items.
     *
     * @param mixed $relations
     * @return $this
     */
    public function with($relations)
    {
        $this->query->with($relations);
        return $this;
    }

    /**
     * @param $column
     * @param null $operator
     * @param null $value
     * @param string $boolean
     * @return $this
     */
    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        $this->query->where($column, $operator, $value, $boolean);
        return $this;
    }

    /**
     * Set default order column and direction for paginator.
     *
     * @param $column
     * @param string $direction
     * @return $this
     */
    public function setDefaultOrderColumns($column, $direction = 'desc')
    {
        $this->defaultOrderColumn = $column;
        $this->defaultOrderDirection = $direction;
        return $this;
    }

    /**
     * @return array
     */
    public function getOrder() {
        // order provided as single string: "column|direction"
        if ($specifiedOrder = $this->param('order')) {
            $parts = preg_split("(\||:)", $specifiedOrder);
            $orderCol = Arr::get($parts, 0, $this->defaultOrderColumn);
            $orderDir = Arr::get($parts, 1, $this->defaultOrderDirection);
        } else {
            $orderCol = $this->param('orderBy', $this->defaultOrderColumn);
            $orderDir = $this->param('orderDir', $this->defaultOrderDirection);
        }

        return ['dir' => $orderDir, 'col' => $orderCol];
    }

    private function toCamelCase($params)
    {
        return collect($params)->keyBy(function($value, $key) {
            return Str::camel($key);
        })->toArray();
    }

    private function applyFilters()
    {
        foreach ($this->filterColumns as $column => $callback) {
            $column = is_int($column) ? $callback : $column;
            $column = Str::camel($column);
            if (isset($this->params[$column])) {
                $value = $this->params[$column];
                $column = Str::snake($column);

                // user specified callback
                if (is_callable($callback)) {
                    $callback($this->query, $value);

                // boolean filter
                } else if ($value === 'false' || $value === 'true') {
                    $this->applyBooleanFilter($column, $value);

                // filter by between date
                } else if (\Str::contains($column, '_at') && \Str::contains($value, ':')) {
                    $this->query()->whereBetween($column, explode(':', $value));

                // filter by specified column value
                } else {
                    $this->query()->where($column, $value);
                }
            }
        }
    }

    /**
     * @param string $column
     * @param string $value
     */
    private function applyBooleanFilter($column, $value)
    {
        // cast "true" or "false" to boolean
        $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
        $casts = $this->model->getCasts();

        // column is a simple boolean type
        if (Arr::get($casts, $column) === 'boolean') {
            $this->query()->where($column, $value);

            // column has actual value, test whether it's null or not by default
        } else {
            if ($value) {
                $this->query()->whereNotNull($column);
            } else {
                $this->query()->whereNull($column);
            }
        }
    }
}
