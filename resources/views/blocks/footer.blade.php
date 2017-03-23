@if (isMobile())
    @foreach($blocks as $block)
    <div>
        <b>{{ $block['title'] }}</b>
    </div>
    <ul class='footer-links'>
        @foreach($block['links'] as $link)
            <li><a href='{{ $link->url }}'>{{ $link->anchor }}</a></li>
        @endforeach
    </ul>
    @endforeach
@else
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
@endif
