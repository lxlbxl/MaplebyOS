<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WebsiteController extends Controller
{
    public function index()
    {
        return view('website.index');
    }

    public function about()
    {
        return view('website.about');
    }

    public function services()
    {
        return view('website.services');
    }

    public function inventory()
    {
        return view('website.inventory');
    }

    public function process()
    {
        return view('website.process');
    }

    public function blog()
    {
        return view('website.blog');
    }

    public function contact()
    {
        return view('website.contact');
    }

    public function shipmentRequest()
    {
        return view('website.shipment-request');
    }

    public function tracking()
    {
        return view('website.tracking');
    }

    public function schedules()
    {
        return view('website.schedules');
    }

    public function valuation()
    {
        return view('website.valuation');
    }

    public function security()
    {
        return view('website.security');
    }

    public function terms()
    {
        return view('website.terms');
    }

    public function blogPost($slug)
    {
        // Simple mapping for the static blog html files
        // In a real app, this would query the database
        $map = [
            'the-future-of-global-shipping' => 'website.blog-post-1',
            'preparing-heavy-machinery' => 'website.blog-post-2',
            'digital-transformation' => 'website.blog-post-3',
        ];

        // Specific handling for static files named blog-post-1.html etc.
        // We can also just return the view if the slug matches the numbering
        if (preg_match('/^blog-post-\d+$/', $slug)) {
            if (view()->exists('website.' . $slug)) {
                return view('website.' . $slug);
            }
        }

        abort(404);
    }
}
