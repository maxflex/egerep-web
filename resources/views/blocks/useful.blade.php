@if($page->useful()->exists())
    @if (isMobile())
        <div>
            <b>Полезное</b>
        </div>
        <div class='footer-links'>
            @foreach($page->useful as $useful)
                <div>
                    <a href="{{ $useful->page->url }}">{{ $useful->text }}</a>
                </div>
            @endforeach
        </div>
    @else
        <b>Полезное</b>
        <ul class='footer-menu'>
            @foreach($page->useful as $useful)
                <li>
                    <a href="{{ $useful->page->url }}">{{ $useful->text }}</a>
                </li>
            @endforeach
        </ul>
    @endif
@endif
