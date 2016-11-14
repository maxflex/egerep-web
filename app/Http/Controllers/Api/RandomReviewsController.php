<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Review;

class RandomReviewsController extends Controller
{
    public function index()
    {
        return Review::get(1, true);
    }
}
