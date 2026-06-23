<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Employee;

class DirectoryController extends Controller
{
    public function index()
    {
        $cities = Branch::orderBy('city')
            ->orderBy('name')
            ->get(['id', 'name', 'city', 'slug', 'photo', 'photo_position_x', 'photo_position_y'])
            ->groupBy('city');

        $theme = Employee::themeFor(Employee::CARD_TYPE_NORMAL);

        return view('directory.index', compact('cities', 'theme'));
    }
}
