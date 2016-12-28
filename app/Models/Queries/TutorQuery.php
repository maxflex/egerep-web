<?php

    namespace App\Models\Queries;

    use App\Models\Tutor;

    class TutorQuery {

        public static function orderByMetroDistance($station_id)
        {
            return
            "IFNULL(
                (
                    select min(distance) from distances
                    where exists (select 1 from tutor_departures td where td.tutor_id = tutors.id and `from` = td.station_id) and `to` = {$station_id}
                ),
                999999
            )";
        }

        public static function orderByMarkerDistance($station_id)
        {
            return
            "IFNULL(
                (
                    select min(d.distance + m.minutes)
                    from markers mr
                    join metros m on m.marker_id = mr.id
                    join distances d on d.from = m.station_id and d.to = {$station_id}
                    where mr.markerable_id = tutors.id and mr.markerable_type = 'App\\\\Models\\\\Tutor' and mr.type='green'
                ),
                999999
            )";
        }
    }
