<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the user dashboard.
     */
    public function index(): View
    {
        return view('dashboard', [
            'user' => auth()->user(),
        ]);
    }
}
