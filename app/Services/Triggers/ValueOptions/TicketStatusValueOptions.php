<?php namespace App\Services\Triggers\ValueOptions;

use App\Services\TagRepository;
use Illuminate\Support\Collection;

class TicketStatusValueOptions implements ValueOptionsInterface  {

    /**
     * TagRepository Instance.
     *
     * @var TagRepository
     */
    private $tagRepository;

    public function __construct(TagRepository $tagRepository)
    {
        $this->tagRepository = $tagRepository;
    }

    /**
     * Get select options for agents:id value
     *
     * @return Collection
     */
    public function getOptions()
    {
        return $this->tagRepository->getByType('status')->map(function($tag) {
            return ['name' => $tag['name'], 'value' => $tag['name']];
        });
    }
}