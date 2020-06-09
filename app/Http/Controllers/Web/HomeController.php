<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Support\Renderable;

class HomeController extends Controller
{
    /**
     * @return Renderable
     */
    public function index(): Renderable {
        return view('admin-pages.home');
    }
}
