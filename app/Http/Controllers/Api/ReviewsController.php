<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Tutor;
use App\Models\Review;
use DB;
use Cache;


class ReviewsController extends Controller
{
    const PER_PAGE = 10;

    public function index(Request $request)
    {
        $take = isset($request->take) ? $request->take : self::PER_PAGE;
        $skip = 3 + (self::PER_PAGE * ($request->page - 1));

        if ($skip < 0) {
            $skip = 0;
        }

        $reviews = Review::has('tutor')->with('tutor')
                ->join('attachments', 'attachments.id', '=', 'reviews.attachment_id')
                ->join('archives', 'archives.attachment_id', '=', 'reviews.attachment_id')
                ->select('reviews.*')
                ->addSelect(DB::raw('attachments.date as attachment_date, archives.date as archive_date'))
                ->addSelect(DB::raw('(SELECT COUNT(*) FROM account_datas ad WHERE ad.tutor_id = attachments.tutor_id AND ad.client_id = attachments.client_id) as lesson_count'))
                ->skip($skip)->take($take)->orderBy('id', 'desc')->get();

        $has_more_reviews = $reviews->count() ? Review::where('id', '<', $reviews->last()->id)->exists() : false;

        return compact('reviews', 'has_more_reviews');
    }
}
