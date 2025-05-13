<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = Client::query();

        if (request()->has('search')) {
            $search = request('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('plate', 'like', "%{$search}%")
                  ->orWhere('insurance_company', 'like', "%{$search}%")
                  ->orWhere('mykad_companyno', 'like', "%{$search}%")
                  ->orWhere('vehicle_model', 'like', "%{$search}%");
            });
        }

        $perPage = request('perPage', 20);
        $clients = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return view('clients.index', compact('clients'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('clients.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'category' => 'nullable|string|max:100',
            'plate' => 'nullable|string|max:50',
            'vehicle_model' => 'nullable|string|max:100',
            'address1' => 'nullable|string',
            'insurance_company' => 'nullable|string|max:255',
            'nettpremium' => 'nullable|numeric',
            'premium' => 'nullable|numeric',
            'expiry_date' => 'nullable|date',
            'renewal_date' => 'nullable|date',
            'status' => 'nullable|in:Active,Expiring,Expired',
            'address2' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'postcode' => 'nullable|string|max:20',
            'mykad_companyno' => 'nullable|string|max:255',
            'inception_date' => 'required|date',
            'reminder_date' => 'nullable|date',
            'document_name' => 'nullable|string|max:255',
            'document_type' => 'nullable|string|max:100',
            'document' => 'nullable|file|max:5120|mimes:pdf,doc,docx,jpg,jpeg,png', // 5MB max
        ]);

        // Convert text fields to uppercase
        $textFields = [
            'name', 'phone', 'category', 'plate', 'vehicle_model', 'address1', 
            'insurance_company', 'address2', 'city', 'state', 'postcode', 'mykad_companyno'
        ];

        foreach ($textFields as $field) {
            if (isset($validated[$field])) {
                $validated[$field] = strtoupper($validated[$field]);
            }
        }

        $client = new Client($validated);

        // Handle document upload
        if ($request->hasFile('document')) {
            $document = $request->file('document');
            $path = $document->store('documents', 'public');
            
            $client->document_name = $document->getClientOriginalName();
            $client->document_path = $path;
            $client->document_type = $document->getClientMimeType();
            $client->document_uploaded_at = now();
        }

        $client->save();

        return redirect()->route('clients.index')
            ->with('success', 'Client created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Client $client)
    {
        return view('clients.show', compact('client'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Client $client)
    {
        return view('clients.edit', compact('client'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'category' => 'required|string|max:255',
            'status' => 'required|string|max:255',
            'plate' => 'nullable|string|max:20',
            'vehicle_model' => 'nullable|string|max:255',
            'insurance_company' => 'nullable|string|max:255',
            'nettpremium' => 'nullable|numeric',
            'premium' => 'nullable|numeric',
            'inception_date' => 'required|date',
            'expiry_date' => 'nullable|date',
            'renewal_date' => 'nullable|date',
            'reminder_date' => 'nullable|date',
            'address1' => 'nullable|string|max:255',
            'address2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'postcode' => 'nullable|string|max:10',
            'mykad_companyno' => 'nullable|string|max:20',
            'document' => 'nullable|file|max:5120|mimes:pdf,doc,docx,jpg,jpeg,png',
        ]);

        // Convert text fields to uppercase
        $textFields = [
            'name', 'phone', 'category', 'plate', 'vehicle_model', 'address1', 
            'insurance_company', 'address2', 'city', 'state', 'postcode', 'mykad_companyno'
        ];

        foreach ($textFields as $field) {
            if (isset($validated[$field])) {
                $validated[$field] = strtoupper($validated[$field]);
            }
        }

        if ($request->hasFile('document')) {
            if ($client->document_path) {
                Storage::delete('public/' . $client->document_path);
            }
            $document = $request->file('document');
            $documentName = $document->getClientOriginalName();
            $documentPath = $document->store('documents', 'public');
            $validated['document_name'] = $documentName;
            $validated['document_path'] = $documentPath;
        }

        $client->update($validated);

        return redirect()->route('clients.show', $client)->with('success', 'Client updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $client)
    {
        // Delete document if exists
        if ($client->document_path) {
            Storage::disk('public')->delete($client->document_path);
        }

        $client->delete();

        return redirect()->route('clients.index')
            ->with('success', 'Client deleted successfully.');
    }

    public function deleteDocument(Client $client)
    {
        if ($client->document_path) {
            Storage::disk('public')->delete($client->document_path);
            
            $client->update([
                'document_name' => null,
                'document_path' => null,
                'document_type' => null,
                'document_uploaded_at' => null,
            ]);
        }

        return redirect()->route('clients.edit', $client)
            ->with('success', 'Document deleted successfully.');
    }

    public function expiring()
    {
        $query = Client::query();

        if (request('search')) {
            $search = request('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('plate', 'like', "%{$search}%")
                  ->orWhere('insurance_company', 'like', "%{$search}%")
                  ->orWhere('mykad_companyno', 'like', "%{$search}%")
                  ->orWhere('vehicle_model', 'like', "%{$search}%");
            });
        }

        // Filter for clients with Expiring status
        $query->where('status', 'Expiring')
              ->orderBy('inception_date', 'desc');

        $perPage = request('perPage', 20);
        $clients = $query->paginate($perPage);

        return view('clients.expiring', compact('clients'));
    }

    public function renew(Client $client)
    {
        $now = now();
        $currentInceptionDate = $client->inception_date;

        // Calculate new inception date
        $newInceptionDate = $now < $currentInceptionDate ? $currentInceptionDate : $now;

        // Calculate new expiry date (1 year from inception, minus 1 day)
        $newExpiryDate = $newInceptionDate->copy()->addYear()->subDay();

        // Calculate new renewal date (1 year from inception)
        $newRenewalDate = $newInceptionDate->copy()->addYear();

        // Calculate new reminder date (1 month before expiry)
        $newReminderDate = $newExpiryDate->copy()->subMonth();

        // Update client with new dates and status
        $client->update([
            'status' => 'Active',
            'inception_date' => $newInceptionDate,
            'expiry_date' => $newExpiryDate,
            'renewal_date' => $newRenewalDate,
            'reminder_date' => $newReminderDate,
        ]);

        return redirect()->route('clients.expiring')
            ->with('success', 'Client insurance has been renewed successfully.');
    }

    public function download()
    {
        $clients = Client::all();
        $filename = 'clients_' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use($clients) {
            $file = fopen('php://output', 'w');
            
            // Add CSV headers
            fputcsv($file, [
                'Name',
                'Phone',
                'Category',
                'Plate',
                'Vehicle Model',
                'Insurance Company',
                'Premium',
                'Nett Premium',
                'Inception Date',
                'Expiry Date',
                'Renewal Date',
                'Status',
                'Address',
                'City',
                'State',
                'Postcode',
                'MyKad/Company No'
            ]);

            // Add data rows
            foreach ($clients as $client) {
                fputcsv($file, [
                    $client->name,
                    $client->phone,
                    $client->category,
                    $client->plate,
                    $client->vehicle_model,
                    $client->insurance_company,
                    $client->premium,
                    $client->nettpremium,
                    $client->inception_date,
                    $client->expiry_date,
                    $client->renewal_date,
                    $client->status,
                    $client->address1 . ' ' . $client->address2,
                    $client->city,
                    $client->state,
                    $client->postcode,
                    $client->mykad_companyno
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
