<div class="hc-sidenav left">
    @foreach($category['children'] as $childCategory)
        <h3 class="highlight-item {{slugify($childCategory['name'])}}">{{$childCategory['name']}}</h3>
        <ul>
            @foreach($childCategory['articles'] as $article)
                <li>
                    <a href="{{'../' . slugify($childCategory['name']) . '/' . slugify($article['title']) . '.html'}}" class="highlight-item {{slugify($article['title'])}}">{{$article['title']}}</a>
                </li>
            @endforeach
        </ul>
    @endforeach
</div>
