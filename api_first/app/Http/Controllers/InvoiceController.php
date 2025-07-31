<?php

namespace App\Http\Controllers;
use App\Models\Company;
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
        // 1. Validate
        $validated = $request->validate([
            'company_id' => 'required|integer|exists:company,id',
            'invoice_id' => 'required|string|max:255',
            'lines'      => 'required|array|min:1',
            'lines.*.description' => 'required|string|max:255',
            'lines.*.qty' => 'required|numeric|min:1',
            'lines.*.price' => 'required|numeric|min:0',
            'lines.*.vat' => 'required|numeric|min:0',
        ]);

        // 2. Get company name
        $company = \DB::table('company')->where('id', $validated['company_id'])->first();
        if (!$company) {
            return back()->withErrors(['company_id' => 'Company not found']);
        }

        // 3. Sanitize lines and calculate totals
        $lines = [];
        $grandTotal = 0;
        foreach ($validated['lines'] as $item) {
            // Sanitize each field
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

        // 4. Store in DB
        \DB::table('invoices')->insert([
            'company_name' => $company->company_name,
            'invoice_id' => htmlspecialchars($validated['invoice_id']),
            'invoice_data' => json_encode($lines),
            'created_at' => now(),
        ]);

        return back()->with('success', 'Invoice saved!');
    }
}
