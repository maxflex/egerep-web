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

        /**
         * $compile_functions – компилировать не только переменные, но и функции
         */
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
                    static::_replace($html, $var, $variable->html);
                }
            }
            return $html;
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
                switch ($function_name) {
                    case 'factory':
                        $replacement = fact(...$args);
                        break;
                    case 'tutor':
                        $replacement = Tutor::find($args[0])->toJson();
                        break;
                    case 'tutors':
                        $replacement = Tutor::bySubject(...$args)->toJson();
                        break;
                    case 'reviews':
                        $replacement = Review::get(...$args)->toJson();
                        break;
                    case 'const':
                        $replacement = Factory::constant($args[0]);
                        break;
                    case 'session':
                        $replacement = json_encode($_SESSION[$args[0]]);
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
                static::_replace($html, $var, $replacement);
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
                    static::_replace($html, $var, $page->{$field});
                }
            }
            static::_replace($html, 'useful', view('blocks.useful', compact('page')));
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

            static::_replace($html, 'current_tutor', $tutor->toJson());
            static::_replace($html, 'similar_tutors', $similar_tutors->toJson());
            static::_replace($html, 'subjects_url', $subjects_url);

            // h1 и desc
            static::_replace($html, 'title', view('tutor.title', compact('tutor')));
            static::_replace($html, 'desc', self::_cleanString(view('tutor.desc', compact('tutor'))));
        }

        /**
         * Компилировать сео-страницу
         * [seo_text_top] или [seo_text_bottom] в зависимости от $page->seo_desktop
         */
        public static function compileSeo($page, &$html)
        {
            if ($page->seo_desktop) {
                static::_replace($html, 'seo_text_top', $page->getClean('html'));
                static::_replace($html, 'seo_text_bottom', '');
            } else {
                static::_replace($html, 'seo_text_top', '');
                static::_replace($html, 'seo_text_bottom', $page->getClean('html'));
            }
        }

        public static function interpolate($text = '')
        {
            return self::START_VAR . $text . self::END_VAR;
        }

        /**
         * Произвести замену переменной в html
         */
        private static function _replace(&$html, $var, $replacement)
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
