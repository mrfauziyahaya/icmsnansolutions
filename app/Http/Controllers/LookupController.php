<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class LookupController extends Controller
{
    public function index(Request $request)
    {
        $ic    = strtoupper(trim($request->query('ic', '')));
        $plate = strtoupper(trim($request->query('plate', '')));

        // If both params supplied (e.g. from WhatsApp link), auto-search
        if ($ic && $plate) {
            $client = Client::where('mykad_companyno', $ic)
                            ->where('plate', $plate)
                            ->first();

            if ($client) {
                return view('lookup.show', compact('client'));
            }

            return view('lookup.index', [
                'ic'     => $ic,
                'plate'  => $plate,
                'error'  => 'No policy found matching those details.',
            ]);
        }

        // IC only — show all vehicles for that IC
        if ($ic) {
            $clients = Client::where('mykad_companyno', $ic)->get();

            if ($clients->count() === 1) {
                $client = $clients->first();
                return view('lookup.show', compact('client'));
            }

            if ($clients->count() > 1) {
                return view('lookup.index', compact('ic', 'plate', 'clients'));
            }

            return view('lookup.index', [
                'ic'    => $ic,
                'plate' => $plate,
                'error' => 'No policy found for that IC / Company No.',
            ]);
        }

        return view('lookup.index', ['ic' => '', 'plate' => '']);
    }
}
