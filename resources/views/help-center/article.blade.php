<html lang="en">
<head>
    <title>{{$article['title']}}</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700,400italic">
    <link rel="stylesheet" href="../../assets/styles/docs-global.css">
    <link rel="stylesheet" href="../../assets/styles/docs-sidenav.css">
    <link rel="stylesheet" href="../../assets/styles/docs-content.css">
    <link rel="stylesheet" href="../../assets/styles/docs-article.css">
    <link rel="stylesheet" href="../../assets/styles/article-content.css">
    <script src="../../assets/docs-scripts.js"></script>
</head>
<body>
<nav class="hc-content-navbar">
    <div class="nav-title">Documentation</div>
    <a class="submit-ticket" href="{{url('help-center/tickets/new')}}">Create a ticket</a>
</nav>

<div class="hc-content">
    @include('help-center.sidenav', ['category' => $category])
    <div class="center">
        <section class="article-container">
            <h1 class="article-title">{{$article['title']}}</h1>
            <div class="article-body">
                {!! $article['body'] !!}
            </div>
        </section>
    </div>
    <div class="right article-content">
        <h4>Content</h4>
    </div>
</div>
</body>
</html>