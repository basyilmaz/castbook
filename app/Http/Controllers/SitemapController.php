<?php

namespace App\Http\Controllers;

use App\Models\Firm;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $pages = [
            [
                'loc' => route('login'),
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ],
            [
                'loc' => route('demo'),
                'changefreq' => 'monthly',
                'priority' => '0.9',
            ],
        ];

        // Authenticated pages are not included in public sitemap
        // Only public-facing pages

        $content = view('sitemap', compact('pages'))->render();

        return response($content, 200)
            ->header('Content-Type', 'application/xml');
    }
}
