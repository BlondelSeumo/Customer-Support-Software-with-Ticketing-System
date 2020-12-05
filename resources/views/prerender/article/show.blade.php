@extends('common::prerender.base')

<?php /** @var Common\Core\Prerender\MetaTags $meta */ ?>

@section('body')
    <h1 class="title">{{$meta->getTitle()}}</h1>

    {!! $meta->getData('article.body') !!}

    <div class="tags">
        @foreach($meta->getData('article.tags') as $tag)
            <span>{{$tag['name']}}</span>
        @endforeach
    </div>
@endsection
