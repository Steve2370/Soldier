<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class GenerateurController extends Controller
{
    public function index(): View
    {
        return view('generateur.index');
    }
}
