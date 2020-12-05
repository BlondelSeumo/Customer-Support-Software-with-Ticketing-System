@extends('common::prerender.base')

<?php /** @var Common\Core\Prerender\MetaTags $meta */ ?>

@section('body')
    <h1>{{ $meta->getTitle() }}</h1>

    @foreach($meta->getData('categories') as $category)
        <section class="category">
            <div class="category-info">
                <h2 class="category-name"><a class="text" href="{{$meta->urls->category($category)}}">{{$category['name']}}</a></h2>

                @if($category['description'])
                    <p class="category-description">{{$category['description']}}</p>
                @endif
            </div>

            @if(isset($category['articles']))
                <div class="articles-list">
                    @foreach($category['articles'] as $article)
                        <a class="article" href="{{$meta->urls->article($article)}}">
                            <span class="text">{{$article['title']}}</span>
                        </a>
                    @endforeach
                </div>
            @endif

            <div class="child-categories">
                @foreach($category['children'] as $child)
                    <div class="child-category">

                        <div class="title">
                            <h3 class="child-category-name">
                                <a href="{{$meta->urls->category($child)}}">{{$child['name']}} ({{$child['articles_count']}})</a>
                            </h3>
                        </div>

                        <div class="articles-list">
                            @foreach($child['articles'] as $article)
                                <a class="article" href="{{$meta->urls->article($article)}}">
                                    <span class="text">{{$article['title']}}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endforeach
@endsection
