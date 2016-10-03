<?php
    namespace App\Models\Service;
    use App\Models\Variable;
    use App\Models\Tutor;
    use App\Models\Client;
    use App\Models\Review;

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
            return $html;
        }

        /**
         * Компилировать страницу препода
         */
        public static function compileTutor($id, &$html)
        {
            $tutor = Tutor::with('markers')->selectDefault()->find($id);
            $similar_tutors = Tutor::getSimilar($tutor);
            static::_replace($html, 'current_tutor', $tutor->toJson());
            static::_replace($html, 'similar_tutors', $similar_tutors->toJson());
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
    }
