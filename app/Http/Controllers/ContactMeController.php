<?php

namespace App\Http\Controllers;

use App\Mail\ContactMe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactMeController extends Controller
{
    public function index()
    {
        return view('contact');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|min:3',
            'email' => 'required|email',
            'message' => 'required|min:10'
        ]);

        Mail::to('admin@mojokre.ge')
            ->send(new ContactMe($request->name, $request->email, $request->message));

        return redirect('/contact')->with('status', 'წერილი გაიგზავნა');
    }
}
