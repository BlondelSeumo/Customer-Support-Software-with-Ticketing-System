<?php namespace App\Services\Triggers\ValueOptions;

use App\Services\TagRepository;
use Illuminate\Support\Collection;

class CategoryTagsValueOptions implements ValueOptionsInterface  {

    /**
     * TagRepository Instance.
     *
     * @var TagRepository
     */
    private $tagRepository;

    /**
     * CategoryTagsValueOptions constructor.
     *
     * @param TagRepository $tagRepository
     */
    public function __construct(TagRepository $tagRepository)
    {
        $this->tagRepository = $tagRepository;
    }

    /**
     * Get select options for category tags value
     *
     * @return Collection
     */
    public function getOptions()
    {
        return $this->tagRepository->getByType('category')->map(function($tag) {
            return ['name' => $tag['name'], 'value' => $tag['name']];
        });
    }
}