@if (isMobile())
    @foreach($blocks as $block)
    <div>
        <b>{{ $block['title'] }}</b>
    </div>
    <ul class='footer-links'>
        @foreach($block['links'] as $link)
            <li ng-hide="$loop->index > \App\Models\Page::MAX_BLOCK_LINKS_MOBILE }}">
                <a href='{{ $link->url }}'>{{ $link->anchor ? $link->anchor : $link->h1 }}</a>
            </li>
        @endforeach
    </ul>
    <span ng-hide="{{ $loop->count < \App\Models\Page::MAX_BLOCK_LINKS_MOBILE }}"
          ng-click="show_block_{{ $loop->index }}_links = !show_block_{{ $loop->index }}_links">
        @{{ show_block_}}{{ $loop->index }}@{{_links ? 'Скрыть' : 'Показать все' }}
    </span>
    @endforeach
@else
    <div class='footer-blocks'>
        <div class="footer-blocks-inner">
            @foreach($blocks as $block)
            <div class="footer-rows">
                <b>{{ $block['title'] }}</b>
                @foreach($block['links'] as $link)
                <div ng-hide="{{ $loop->index > \App\Models\Page::MAX_BLOCK_LINKS }}">
                    <a href='{{ $link->url }}'>{{ $link->anchor ? $link->anchor : $link->h1 }}</a>
                </div>
                @endforeach
            </div>
            @endforeach
        </div>
    </div>
@endif
