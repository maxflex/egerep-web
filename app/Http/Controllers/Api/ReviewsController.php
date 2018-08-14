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
    const PER_PAGE      = 10;   //
    const FIRST_PAGE    = 3;    // сколько отображать в начале

    public function index(Request $request)
    {
        $take = $request->page == 0 ? self::FIRST_PAGE : self::PER_PAGE;
        $skip = self::FIRST_PAGE + (self::PER_PAGE * ($request->page - 1));

        if ($skip < 0) {
            $skip = 0;
        }

        // attachment-refactored
        $reviews = Review::has('tutor')->with('tutor')
            // ->select('reviews.*', DB::raw('(reviews.ball / reviews.max_ball) as ball_efficency'))
            ->skip($skip)->take($take)->orderBy('created_at', 'desc')
            ->get();

        $has_more_reviews = $reviews->count() ? Review::where('id', '<', $reviews->last()->id)->exists() : false;

        return compact('reviews', 'has_more_reviews');
    }
}
