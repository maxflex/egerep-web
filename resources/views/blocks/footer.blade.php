@if (isMobile())
    @foreach($blocks as $block)
    <div>
        <b>{{ $block['title'] }}</b>
    </div>
    <ul class='footer-links' ng-init="toggler_{{ $loop->index }} = {{ count($block['links']) }} > {{ \App\Models\Page::MAX_BLOCK_LINKS_MOBILE }}; show_block_items_{{ $loop->index }} = !toggler_{{$loop->index}}">
        @foreach($block['links'] as $link)
            <li @if ($loop->index > \App\Models\Page::MAX_BLOCK_LINKS_MOBILE) ng-show="show_block_items_{{ $loop->parent->index }}" @endif>
                <a href='{{ $link->url }}'>{{ $link->anchor ? $link->anchor : $link->getClean('h1') }}</a>
            </li>
        @endforeach
    </ul>
    <span class="toggler"
          ng-show="toggler_{{ $loop->index }} && !show_block_items_{{ $loop->index }}"
          ng-click="show_block_items_{{ $loop->index }} = !show_block_items_{{ $loop->index }}">
          <span>Показать все</span> <i class="fa fa-angle-down" aria-hidden="true"></i>
    </span>
    <span class="toggler"
          ng-show="toggler_{{ $loop->index }} && show_block_items_{{ $loop->index }}"
          ng-click="show_block_items_{{ $loop->index }} = !show_block_items_{{ $loop->index }}">
          <span>Скрыть</span> <i class="fa fa-angle-up" aria-hidden="true"></i>
    </span>
    @endforeach
@else
    <div class='footer-blocks'>
        <div class="footer-blocks-inner">
            @foreach($blocks as $block)
            <div class="footer-rows"
                ng-init="toggler_{{ $loop->index }} = {{ count($block['links']) }} > {{ \App\Models\Page::MAX_BLOCK_LINKS }}; show_block_items_{{ $loop->index }} = !toggler_{{$loop->index}}">
                <b>{{ $block['title'] }}</b>
                @foreach($block['links'] as $link)
                    <div @if ($loop->index > \App\Models\Page::MAX_BLOCK_LINKS) ng-show="show_block_items_{{ $loop->parent->index }}" @endif>
                    <a href='{{ $link->url }}'>{{ $link->anchor ? $link->anchor : $link->getClean('h1') }}</a>
                </div>
                @endforeach
            </div>
            <span class="toggler"
                  ng-show="toggler_{{ $loop->index }} && !show_block_items_{{ $loop->index }}"
                  ng-click="show_block_items_{{ $loop->index }} = !show_block_items_{{ $loop->index }}">
                  <span>Показать все</span> <i class="fa fa-angle-down" aria-hidden="true"></i>
            </span>
            <span class="toggler"
                  ng-show="toggler_{{ $loop->index }} && show_block_items_{{ $loop->index }}"
                  ng-click="show_block_items_{{ $loop->index }} = !show_block_items_{{ $loop->index }}">
                  <span>Скрыть</span> <i class="fa fa-angle-up" aria-hidden="true"></i>
            </span>
            @endforeach
        </div>
    </div>
@endif
