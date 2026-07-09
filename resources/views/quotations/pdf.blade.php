<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Quotation v{{ $quotation->version }}</title>
    <style>
        body { font-family: sans-serif; font-size: 13px; color: #1f2937; }
        h1 { font-size: 20px; margin-bottom: 0; }
        .muted { color: #6b7280; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 8px; border-bottom: 1px solid #e5e7eb; text-align: left; }
        th { background: #f3f4f6; }
        .text-right { text-align: right; }
        .totals { margin-top: 16px; width: 300px; margin-left: auto; }
        .totals td { border: none; padding: 4px 8px; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 4px; background: #ccfbf1; color: #0f766e; font-size: 11px; }
    </style>
</head>
<body>
    <h1>Quotation</h1>
    <p class="muted">Version {{ $quotation->version }} &middot; <span class="badge">{{ $quotation->status->getLabel() }}</span></p>

    <p>
        <strong>{{ $quotation->deal->lead->contact->company->name }}</strong><br>
        Attn: {{ $quotation->deal->lead->contact->name }}<br>
        @if($quotation->deal->lead->contact->email)
            {{ $quotation->deal->lead->contact->email }}<br>
        @endif
        @if($quotation->deal->lead->contact->phone)
            {{ $quotation->deal->lead->contact->phone }}
        @endif
    </p>

    <table>
        <thead>
        <tr>
            <th>Product</th>
            <th class="text-right">Quantity</th>
            <th class="text-right">Unit Price</th>
            <th class="text-right">Subtotal</th>
        </tr>
        </thead>
        <tbody>
        @foreach($quotation->items as $item)
            <tr>
                <td>{{ $item->product->name }}</td>
                <td class="text-right">{{ $item->quantity }}</td>
                <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                <td class="text-right">{{ number_format($item->subtotal(), 2) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td>Discount</td>
            <td class="text-right">{{ number_format($quotation->discount_percent, 2) }}%</td>
        </tr>
        <tr>
            <td><strong>Total</strong></td>
            <td class="text-right"><strong>{{ number_format($quotation->total_value, 2) }}</strong></td>
        </tr>
    </table>

    <p class="muted" style="margin-top: 40px;">Generated {{ now()->format('Y-m-d H:i') }}</p>
</body>
</html>
