@if($page->useful()->exists())
    <b>Полезное</b>
    <ul class='footer-menu'>
        @foreach($page->useful as $useful)
            <li>
                <a href="{{ $useful->page->url }}">{{ $useful->text }}</a>
            </li>
        @endforeach
    </ul>
@endif
