<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Bill of Lading {{ $bol->bol_number }}</title>
    {{-- dompdf supports a limited CSS subset — keep it simple and table-based. --}}
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { font-size: 11px; color: #111; margin: 0; }
        h1 { font-size: 18px; margin: 0 0 2px; }
        .muted { color: #555; }
        .row { width: 100%; }
        table { width: 100%; border-collapse: collapse; }
        .box { border: 1px solid #333; padding: 6px; vertical-align: top; }
        .section-title { font-weight: bold; text-transform: uppercase; font-size: 9px; color: #555; margin-bottom: 3px; }
        .freight th, .freight td { border: 1px solid #333; padding: 4px 6px; text-align: left; }
        .freight th { background: #eee; font-size: 9px; text-transform: uppercase; }
        .header td { padding-bottom: 8px; }
        .terms { margin-top: 10px; font-size: 9px; color: #444; }
    </style>
</head>
<body>
    <table class="header">
        <tr>
            <td>
                <h1>Bill of Lading</h1>
                <div class="muted">Non-Negotiable</div>
            </td>
            <td style="text-align: right;">
                <div><strong>BOL #:</strong> {{ $bol->bol_number }}</div>
                <div class="muted">Date: {{ optional($bol->generated_at ?? $bol->created_at)->format('Y-m-d') }}</div>
            </td>
        </tr>
    </table>

    <table class="row">
        <tr>
            <td class="box" style="width: 50%;">
                <div class="section-title">Ship From</div>
                @include('bol.partials.address', ['address' => $bol->ship_from])
            </td>
            <td class="box" style="width: 50%;">
                <div class="section-title">Ship To</div>
                @include('bol.partials.address', ['address' => $bol->ship_to])
            </td>
        </tr>
    </table>

    <table class="row" style="margin-top: 8px;">
        <tr>
            <td class="box" style="width: 50%;">
                <div class="section-title">Customer</div>
                {{ $bol->customer_name ?? '—' }}
            </td>
            <td class="box" style="width: 50%;">
                <div class="section-title">Carrier</div>
                {{ $bol->carrier_name ?? '—' }}
            </td>
        </tr>
    </table>

    <table class="freight" style="margin-top: 10px;">
        <thead>
            <tr>
                <th>Description</th>
                <th>Pieces</th>
                <th>Weight (lbs)</th>
                <th>Class</th>
            </tr>
        </thead>
        <tbody>
            @forelse($bol->freight ?? [] as $item)
                <tr>
                    <td>{{ $item['description'] ?? '' }}</td>
                    <td>{{ $item['pieces'] ?? '' }}</td>
                    <td>{{ $item['weight_lbs'] ?? '' }}</td>
                    <td>{{ $item['freight_class'] ?? '' }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="muted">No freight items recorded.</td></tr>
            @endforelse
        </tbody>
    </table>

    @if($bol->special_instructions)
        <div class="terms"><strong>Special Instructions:</strong> {{ $bol->special_instructions }}</div>
    @endif

    <div class="terms">
        RECEIVED, subject to individually determined rates or contracts that have
        been agreed upon in writing between the carrier and shipper, the property
        described above in apparent good order.
    </div>
</body>
</html>
