<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /** Atterraggio post-login: ogni ruolo ha la propria home. */
    public function index(Request $request)
    {
        return redirect()->route($request->user()->role->homeRoute());
    }

    /** Apre la notifica segnandola come letta e portando alla scheda collegata. */
    public function openNotification(Request $request, Notification $notification)
    {
        abort_unless($notification->user_id === $request->user()->id, 403);

        $notification->forceFill(['read_at' => now()])->save();

        return redirect()->to($notification->url ?? route('home'));
    }
}
