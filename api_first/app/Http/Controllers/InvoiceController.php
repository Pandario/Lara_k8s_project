<?php

namespace App\Http\Controllers;
use App\Models\Company;
use App\Models\ApiLog;
use Illuminate\Http\Request;


class InvoiceController extends Controller
{
    public function create()
    {
        $companies = Company::orderBy('company_name')->get();
        return view('invoice.create', compact('companies'));
    }

    public function store(Request $request)
{
    // Always log the attempt (sanitized)
    ApiLog::create([
        'level' => 'info',
        'message' => 'Attempt to create invoice',
        'context' => json_encode([
            'company_id' => $request->input('company_id'),
            'invoice_id' => $request->input('invoice_id'),
            'lines' => $request->input('lines'),
            'time' => now()->toDateTimeString()
        ])
    ]);

    try {
        $validated = $request->validate([
            'company_id' => 'required|integer|exists:company,id',
            'invoice_id' => 'required|string|max:255',
            'lines'      => 'required|array|min:1',
            'lines.*.description' => 'required|string|max:255',
            'lines.*.qty' => 'required|numeric|min:1',
            'lines.*.price' => 'required|numeric|min:0',
            'lines.*.vat' => 'required|numeric|min:0',
        ]);

        $company = \DB::table('company')->where('id', $validated['company_id'])->first();
        if (!$company) {
            ApiLog::create([
                'level' => 'error',
                'message' => 'Company not found for ID: ' . $validated['company_id'],
                'context' => json_encode(['time' => now()->toDateTimeString()])
            ]);
            return back()->withErrors(['company_id' => 'Company not found']);
        }

        $lines = [];
        $grandTotal = 0;
        foreach ($validated['lines'] as $item) {
            $desc = htmlspecialchars(preg_replace('/[^a-zA-Z0-9\s\-,.]/u', '', $item['description']));
            $qty = (float) $item['qty'];
            $price = (float) $item['price'];
            $vat = (float) $item['vat'];
            $lineTotal = $qty * $price * (1 + $vat / 100);

            $grandTotal += $lineTotal;
            $lines[] = [
                'description' => $desc,
                'qty' => $qty,
                'price' => $price,
                'vat' => $vat,
                'lineTotal' => round($lineTotal, 2)
            ];
        }
        // Add total as the last object
        $lines[] = ['total_incl_vat' => round($grandTotal, 2)];

        \DB::table('invoices')->insert([
            'company_name' => $company->company_name,
            'invoice_id' => htmlspecialchars($validated['invoice_id']),
            'invoice_data' => json_encode($lines),
            'created_at' => now(),
        ]);

        ApiLog::create([
            'level' => 'info',
            'message' => 'Invoice created',
            'context' => json_encode([
                'company_id' => $validated['company_id'],
                'invoice_id' => $validated['invoice_id'],
                'total' => round($grandTotal, 2),
                'time' => now()->toDateTimeString()
            ])
        ]);

        return back()->with('success', 'Invoice saved!');
    } catch (\Exception $e) {
        ApiLog::create([
            'level' => 'error',
            'message' => 'Invoice creation failed: ' . htmlspecialchars($e->getMessage()),
            'context' => json_encode([
                'invoice_id' => $request->input('invoice_id'),
                'time' => now()->toDateTimeString()
            ])
        ]);
        return back()->withErrors(['error' => 'Something went wrong.']);
    }
}
}
