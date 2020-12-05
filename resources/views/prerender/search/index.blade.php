@extends('common::prerender.base')

<?php /** @var Common\Core\Prerender\MetaTags $meta */ ?>

@section('body')
    <h1>{{ $meta->getTitle() }}</h1>

    <div class="articles">
        @foreach($meta->getData('pagination') as $article)
            <div class="article">
                <a class="title" href="{{$meta->urls->article($article)}}">{{$article['title']}}</a>
                <div class="body">{{$article['body']}}</div>

                @if($articleCategories = $article['categories']->toArray() && !empty($articleCategories))
                    @if ($articleCategories[0] && $articleCategories[0]['parent'])
                        <div class="parent-category">
                            <a class="parent" href="{{$meta->urls->category($articleCategories[0]['parent'])}}">{{$articleCategories[0]['parent']['name']}}</a>
                        </div>
                    @endif

                    <div class="child-category">
                        <a class="child" href="{{$meta->urls->category($articleCategories[0])}}">{{$articleCategories[0]['name']}}</a>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
@endsection