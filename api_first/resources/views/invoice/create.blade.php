<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Create Invoice</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="p-6 bg-gray-100">
<div class="max-w-4xl mx-auto bg-white p-6 rounded shadow" x-data="invoiceForm()">
    <h1 class="text-2xl font-bold mb-4">Create Invoice</h1>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('invoice.store') }}">
        @csrf

        <!-- Company dropdown -->
        <label class="block mb-2 font-semibold">Company</label>
        <select name="company_id" class="border p-2 w-full mb-6 rounded" required>
            @foreach($companies as $c)
                <option value="{{ $c->id }}">{{ $c->company_name }}</option>
            @endforeach
        </select>

        <!-- Invoice ref -->
        <label class="block mb-2 font-semibold">Invoice ID</label>
        <input name="invoice_id" x-model="invoice_id" class="border p-2 w-full mb-6 rounded" placeholder="INV-12345" required />

        <!-- Line-item table -->
        <table class="w-full text-sm mb-4">
            <thead class="bg-gray-200">
            <tr>
                <th class="p-2">Description</th>
                <th class="p-2">Qty</th>
                <th class="p-2">Price (€)</th>
                <th class="p-2">VAT %</th>
                <th class="p-2 text-right">Line Total</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <template x-for="(item, idx) in items" :key="idx">
                <tr>
                    <td>
                        <input :name="`lines[${idx}][description]`" x-model="item.desc" class="border p-1 w-full" required>
                    </td>
                    <td>
                        <input type="number" min="1" :name="`lines[${idx}][qty]`" x-model.number="item.qty" class="border p-1 w-20" required>
                    </td>
                    <td>
                        <input type="number" step="0.01" min="0" :name="`lines[${idx}][price]`" x-model.number="item.price" class="border p-1 w-24" required>
                    </td>
                    <td>
                        <input type="number" step="1" min="0" :name="`lines[${idx}][vat]`" x-model.number="item.vat" class="border p-1 w-20" required>
                    </td>
                    <td class="text-right pr-2" x-text="lineTotal(item).toFixed(2)"></td>
                    <td class="pl-2">
                        <button type="button" @click="items.splice(idx,1)" class="text-red-600">✕</button>
                    </td>
                </tr>
            </template>
            </tbody>
        </table>

        <button type="button" @click="addLine" class="bg-blue-600 text-white px-3 py-1 rounded mb-4">Add line</button>

        <!-- Totals grouped by VAT -->
        <div class="border-t pt-4">
            <template x-for="(group, rate) in vatGroups()" :key="rate">
                <div class="flex justify-between">
                    <span>Subtotal (VAT <span x-text="rate"></span>%):</span>
                    <span>€ <span x-text="group.sub.toFixed(2)"></span></span>
                </div>
            </template>
            <div class="flex justify-between font-semibold mt-2">
                <span>Total (incl. VAT):</span>
                <span>€ <span x-text="grandTotal().toFixed(2)"></span></span>
            </div>
        </div>

        <button type="submit" class="mt-6 bg-green-500 text-white font-bold px-4 py-2 rounded">Send</button>
    </form>
</div>
</body>
</html>