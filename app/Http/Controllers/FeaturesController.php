<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FeaturesController extends Controller
{
    /**
     * Display the features index page
     */
    public function index()
    {
        return Inertia::render('Features/index', [
            'features' => $this->getFeatures()
        ]);
    }

    /**
     * Display a specific feature page
     */
    public function show($id)
    {
        $features = $this->getFeatures();
        $feature = collect($features)->firstWhere('id', $id);

        if (!$feature) {
            return redirect()->route('features.index');
        }

        return Inertia::render('Features/Show', [
            'feature' => $feature,
            'features' => $features
        ]);
    }

    /**
     * Get features data
     * Pada implementasi nyata, ini mungkin berasal dari database
     */
    private function getFeatures()
    {
        $features = Setting::query()
            ->orderBy('id')
            ->get()
            ->map(function ($item, $index) {
                return [
                    'id' => $item->slug,
                    'title' => $item->title,
                    'shortDescription' => $item->short_description,
                    'longDescription' => $item->long_description,
                    'usages' => $item->usage,
                    'number' => $index + 1,
                    'color' => $item->color,
                    'image' => $item->image ?? '/api/placeholder/600/400',
                ];
            })
            ->toArray();

        return $features;
    }
}
