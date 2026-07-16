@php($address = $address ?? [])
@if(empty($address))
    <span class="muted">—</span>
@else
    @if(!empty($address['name']))<div>{{ $address['name'] }}</div>@endif
    <div>{{ $address['address_line1'] ?? '' }}</div>
    @if(!empty($address['address_line2']))<div>{{ $address['address_line2'] }}</div>@endif
    <div>
        {{ $address['city'] ?? '' }}, {{ $address['state'] ?? '' }} {{ $address['postal_code'] ?? '' }}
    </div>
    @if(!empty($address['contact_name']))
        <div class="muted">{{ $address['contact_name'] }} · {{ $address['contact_phone'] ?? '' }}</div>
    @endif
@endif
