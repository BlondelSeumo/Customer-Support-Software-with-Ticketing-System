@extends('common::prerender.base')

<?php /** @var Common\Core\Prerender\MetaTags $meta */ ?>

@section('body')
    <h1 class="title">{{$meta->getTitle()}}</h1>

    <p>{{$meta->getDescription()}}</p>

    <div class="children">
        @foreach($meta->getData('category.children') as $child)
            <div class="child">
                <h2><a href="{{$meta->urls->category($child)}}">{{$child['name']}}</a></h2>
            </div>
        @endforeach
    </div>

    @if($articles = $meta->getData('category.articles'))
        <div class="articles">
            @foreach($articles as $article)
                <div class="article">
                    <h2><a href="{{$meta->urls->article($article)}}">{{$article['title']}}</a></h2>
                </div>
            @endforeach
        </div>
    @endif
@endsection
