setTimeout(function() {
    // article content
    var regExp = /<(h[2|3]) id=".*?">(.*?)<\/h[2|3]>/g;
    var body = document.querySelector('.article-body').innerHTML;
    var content = document.querySelector('.article-content');
    var match = regExp.exec(body);
    while (match != null) {
        var link = document.createElement('a');
        link.href = '#' + match[1];

        var heading = match[2];
        if (heading.indexOf('<a') === 0) {
            heading = heading.match(/<a href=".*?">(.*?)<\/a>/)[1];
        }
        link.textContent = heading;

        content.appendChild(link);
        match = regExp.exec(body);
    }

    // sidenav highlight
    var el = document.querySelector('.highlight-item.active');
    el && el.classList.remove('active');

    // article-name.html => article-name
    var name = window.location.pathname.split('/').pop().replace('.html', '');
    var el  = document.querySelector('.highlight-item.' + name);
    el && el.classList.add('active');
});
