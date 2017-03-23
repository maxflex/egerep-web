<div class='footer-blocks'>
    <div class="footer-blocks-inner">
        @foreach($blocks as $block)
        <div class="footer-rows">
            <b>{{ $block['title'] }}</b>
            @foreach($block['links'] as $link)
            <div>
                <a href='{{ $link->url }}'>{{ $link->anchor }}</a>
            </div>
            @endforeach
        </div>
        @endforeach
    </div>
</div>
