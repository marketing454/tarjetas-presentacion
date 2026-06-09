<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CardTypeBanner;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CardTypeBannerController extends Controller
{
    public function index()
    {
        $banners = CardTypeBanner::all()->keyBy('card_type');

        return view('admin.card-banners.index', compact('banners'));
    }

    public function update(Request $request)
    {
        $rules = [];

        foreach (array_keys(Employee::cardTypes()) as $cardType) {
            $rules["banners.{$cardType}"] = 'nullable|image|mimes:jpeg,png,jpg,webp|max:8192';
            $rules["remove.{$cardType}"] = 'nullable|boolean';
        }

        $request->validate($rules);

        foreach (array_keys(Employee::cardTypes()) as $cardType) {
            $banner = CardTypeBanner::firstOrNew(['card_type' => $cardType]);

            if ($request->boolean("remove.{$cardType}")) {
                if ($banner->exists && $banner->banner_path) {
                    Storage::disk('public')->delete($banner->banner_path);
                }
                if ($banner->exists) {
                    $banner->delete();
                }
                continue;
            }

            if ($request->hasFile("banners.{$cardType}")) {
                if ($banner->exists && $banner->banner_path) {
                    Storage::disk('public')->delete($banner->banner_path);
                }

                $banner->banner_path = $request->file("banners.{$cardType}")
                    ->store('card-type-banners', 'public');
                $banner->save();
            }
        }

        return redirect()->route('admin.card-banners.index')
            ->with('success', 'Banners predeterminados actualizados correctamente.');
    }
}
