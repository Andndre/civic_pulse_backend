<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class AdminWebController extends Controller
{
    /**
     * Display the Admin Login page.
     */
    public function login(): View
    {
        return view('admin.login');
    }

    /**
     * Display the Admin Dashboard.
     */
    public function dashboard(): View
    {
        return view('admin.dashboard');
    }
}
