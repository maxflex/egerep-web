<?php
    namespace App\Models\Service;
    use App\Models\Variable;
    use App\Models\Tutor;
    use App\Models\Client;
    use App\Models\Review;
    use App\Models\Page;

    /**
     * Parser
     */
    class Parser
    {
        const START_VAR = '[';
        const END_VAR   = ']';

        const BLOCKS = [
            1 => 'Репетиторы по метро и районам',
            2 => 'Репетиторы по вузам',
            3 => 'Остальное',
        ];

        static $cached_functions = [
            'factory',
            'tutor',
            'tutors',
            'reviews',
            'const',
            'subject',
            'link',
            'count'
        ];

        public static function compileVars($html)
        {
            preg_match_all('#\\' . static::interpolate('[\S]+\\') . '#', $html, $matches);
            $vars = $matches[0];
            foreach ($vars as $var) {
                $var = trim($var, static::interpolate());
                static::compileFunctions($html, $var);
                $query = Variable::findByName($var);
                if ($query->exists()) {
                    $variable = $query->first();
                    static::replace($html, $var, $variable->html);
                }
            }

            static::compileBlocks($html);

            return $html;
        }

        private static function compileBlocks(&$html)
        {
            // @todo: сделать красивее
            if (isIndexPage()) {
                $blocks = [];
                foreach(range(1, 3) as $index) {
                    $links = Page::getBlockLinks($index);
                    if (count($links)) {
                        $blocks[] = [
                            'title' => static::BLOCKS[$index],
                            'links' => $links,
                        ];
                    }
                }
                static::replace($html, 'footer-blocks', view('blocks.footer', compact('blocks')));
            }
            else if (! isSerpPage()) {
                static::replace($html, 'footer-blocks', '');
            }
        }
        /**
         * Компилирует функции типа [factory|subjects|name]
         */
        public static function compileFunctions(&$html, $var)
        {
            $replacement = '';
            $args = explode('|', $var);
            if (count($args) > 1) {
                $function_name = $args[0];
                array_shift($args);

                if (! ($replacement = ParserCache::get($var))) {
                    $ignore_cache = false;
                    switch ($function_name) {
                        case 'mobile':
                            $replacement = isMobile($args[0] == 'raw');
                            break;
                        case 'factory':
                            $replacement = fact(...$args);
                            break;
                        case 'tutor':
                            $replacement = Tutor::find($args[0])->toJson();
                            break;
                        case 'elixir':
                            $replacement = elixir($args[0]);
                            break;
                        case 'tutors':
                            $replacement = Tutor::bySubject(...$args)->toJson();
                            break;
                        // is|test
                        case 'is':
                            $replacement = (isTestSubdomain() || isDevSubdomain()) ? 'true' : 'false';
                            break;
                        case 'ab-test-if':
                            $key = 'ab-test-' . $args[0];
                            $replacement = @$_COOKIE[$key] == 1 ? 'true' : 'false';
                            break;
                        case 'reviews':
                            if ($args[0] === 'random') {
                                $ignore_cache = true;
                                $replacement = Review::get(1, true)->toJson();
                            } else {
                                $replacement = Review::get(...$args)->toJson();
                            }
                            break;
                        case 'const':
                            $replacement = Factory::constant($args[0]);
                            break;
                        case 'session':
                            $replacement = json_encode(@$_SESSION[$args[0]]);
                            break;
                        case 'param':
                            $replacement = json_encode(@$_GET[$args[0]]);
                            break;
                        case 'subject':
                            $replacement = json_encode(Page::getSubjectRoutes());
                            break;
                        case 'link':
                            // получить ссылку либо по [link|id_раздела] или по [link|math]
                            $replacement = is_numeric($args[0]) ? Page::getUrl($args[0]) : Page::getSubjectUrl($args[0]);
                            break;
                        case 'count':
                            $type = array_shift($args);
                            switch($type) {
                                case 'tutors':
                                    $replacement = Tutor::count(...$args);
                                    break;
                                case 'clients':
                                    $replacement = Client::count();
                                    break;
                                case 'reviews':
                                    $replacement = Review::count();
                                    break;
                            }
                            break;
                    }

                    if (! $ignore_cache) {
                        ParserCache::set($var, $replacement);
                    }
                }
                static::replace($html, $var, $replacement);
            }
        }

        /**
         * Компиляция значений страницы
         * значения типа [page.h1]
         */
        public static function compilePage($page, $html)
        {
            preg_match_all('#\\' . static::interpolate('page\.[\S]+\\') . '#', $html, $matches);
            $vars = $matches[0];
            foreach ($vars as $var) {
                $var = trim($var, static::interpolate());
                $field = explode('.', $var)[1];
                if ($page->{$field}) {
                    static::replace($html, $var, $page->{$field});
                }
            }
            return $html;
        }

        /**
         * Компилировать страницу препода
         */
        public static function compileTutor($id, &$html)
        {
            $tutor = Tutor::with('markers')->selectDefault()->find($id);
            $similar_tutors = Tutor::getSimilar($tutor);

            // Ссылка «все репетиторы по ...»
            $subjects_url = Page::getUrl(@Page::$subject_page_id[implode(',', $tutor->subjects)]);

            static::replace($html, 'current_tutor', $tutor->toJson());
            static::replace($html, 'similar_tutors', $similar_tutors->toJson());
            static::replace($html, 'subjects_url', $subjects_url);

            // h1 и desc
            static::replace($html, 'title', view('tutor.title', compact('tutor')));
            static::replace($html, 'desc', self::_cleanString(view('tutor.desc', compact('tutor'))));
            static::replace($html, 'footer-blocks', '');
        }

        /**
         * Компилировать сео-страницу
         */
        public static function compileSeo($page, &$html)
        {
            static::replace($html, 'seo_text', "<div class='seo-text'>" . $page->getClean('html') . "</div>");
            static::compileLinks($html);
            // detect page refresh
            static::replace($html, 'page_was_refreshed', (int)(
                (isset($_SESSION['page_was_refreshed']) || (isset($_SERVER['HTTP_CACHE_CONTROL']) && $_SERVER['HTTP_CACHE_CONTROL'] === 'max-age=0') || $page->isMainSerp())
            ));

            unset($_SESSION['page_was_refreshed']);

            // @todo: сделать красивее
            $links = Page::getPageLinks($page->id);
            if (count($links)) {
                $blocks = [[
                    'title' => static::BLOCKS[3],
                    'links' => $links,
                ]];
                static::replace($html, 'footer-blocks', view('blocks.footer', compact('blocks')));
            } else {
                static::replace($html, 'footer-blocks', '');
            }
        }

        public static function compileLinks(&$html)
        {
            preg_match_all('#\\' . static::interpolate('link\|[\d]+\\') . '#', $html, $matches);
            $vars = $matches[0];
            foreach ($vars as $var) {
                $var = trim($var, static::interpolate());
                $page_id = explode('|', $var)[1];
                static::replace($html, $var, Page::getUrl($page_id));
            }
        }

        public static function interpolate($text = '')
        {
            return self::START_VAR . $text . self::END_VAR;
        }

        /**
         * Произвести замену переменной в html
         */
        public static function replace(&$html, $var, $replacement)
        {
            $html = str_replace(static::interpolate($var), $replacement, $html);
        }

        /**
         * Заменить переносы строки и двойные пробелы,
         * а так же пробел перед запятыми, пробелы на краях
         */
        private static function _cleanString($text)
        {
            return str_replace(' ,', ',',
                trim(
                    preg_replace('!\s+!', ' ',
                        str_replace(PHP_EOL, ' ', $text)
                    )
                )
            );
        }
    }
