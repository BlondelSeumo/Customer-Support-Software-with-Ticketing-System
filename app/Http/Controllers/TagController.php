<?php namespace App\Http\Controllers;

use App\Services\TagRepository;
use App\Tag;
use Common\Core\BaseController;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TagController extends BaseController
{
    /**
     * @var Tag
     */
    private $tag;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var TagRepository
     */
    private $tagRepository;

    /**
     * @param Request $request
     * @param Tag $tag
     * @param TagRepository $tagRepository
     */
    public function __construct(Request $request, Tag $tag, TagRepository $tagRepository)
    {
        $this->tag     = $tag;
        $this->request = $request;
        $this->tagRepository = $tagRepository;
    }

    /**
     * Fetch tags needed to display agent mailbox.
     *
     * @return Collection
     */
    public function tagsForAgentMailbox()
    {
        return $this->tagRepository->getStatusAndCategoryTags();
    }

    /**
     * Create a new tag.
     *
     * @return JsonResponse
     */
    public function store()
    {
        $this->authorize('store', Tag::class);

        $this->validate($this->request, $this->tagRepository->getValidationRules('store'));

        $tag = $this->tagRepository->create($this->request->all());

        return $this->success(['tag' => $tag], 201);
    }

    /**
     * @param integer $id
     * @return JsonResponse
     */
    public function update($id)
    {
        $tag = $this->tagRepository->findOrFail($id);

        $this->authorize($tag);

        $this->validate($this->request, $this->tagRepository->getValidationRules('update', $tag->id));

        $tag = $this->tagRepository->update($tag, $this->request->all());

        return $this->success(['tag' => $tag]);
    }

    /**
     * @return JsonResponse
     */
    public function deleteMultiple()
    {
        $this->authorize('destroy', Tag::class);

        $this->validate($this->request, [
            'ids' => 'required|array|min:1'
        ]);

        $this->tagRepository->deleteMultiple($this->request->get('ids'));

        return $this->success([], 204);
    }
}
