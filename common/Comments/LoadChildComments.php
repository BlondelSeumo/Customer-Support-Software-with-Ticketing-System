<?php

namespace Common\Comments;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class LoadChildComments
{
    /**
     * @param Model $commentable
     * @param Collection $rootComments
     * @return array
     */
    public function execute(Model $commentable, Collection $rootComments)
    {
        $paths = $rootComments->map(function(Comment $comment) {
            $path = $comment->getRawOriginal('path');
            return "LIKE '$path%'";
        })->implode(' OR path ');

        $childComments = app(Comment::class)
            ->with(['user' => function(BelongsTo $builder) {
               $builder->compact();
            }])
            ->where('commentable_id', $commentable->id)
            ->where('commentable_type', get_class($commentable))
            ->childrenOnly()
            ->where(function(Builder $builder) use($paths) {
                $builder->whereRaw("path $paths");
            })
            ->orderBy('path', 'asc')
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get()
            ->map(function(Comment $comment) {
                $comment->relative_created_at = $comment->created_at->diffForHumans();
                return $comment;
            });

        $childComments->each(function($childComment) use($rootComments) {
            $index = $rootComments->search(function($comment) use($childComment) {
                return $comment['id'] === $childComment['parent_id'];
            });
            $rootComments->splice($index + 1, 0, [$childComment]);
        });

        return $rootComments->toArray();
    }
}
