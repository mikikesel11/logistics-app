# Domain Model

The MVP delivers the CRM + Bill of Lading core. Every tenant-scoped table
carries `organization_id` and is filtered by a global Eloquent scope
([ADR 0002](adr/0002-single-tenant-now-multi-later.md)).

## Aggregates

| Model | Table | Purpose |
|---|---|---|
| `Organization` | `organizations` | The tenant. One seeded now; every other row belongs to one. |
| `User` | `users` | Broker staff. Roles via spatie (`admin`, `broker`, `dispatcher`, `viewer`). |
| `Customer` | `customers` | Shipper. Billing terms, credit limit; has many `Contact`s. |
| `Carrier` | `carriers` | Trucking company. `mc_number` (unique per org), `dot_number`, insurance expiry; has many `Contact`s. |
| `Contact` | `contacts` | Person, polymorphic (`contactable`) to a Customer or Carrier. |
| `Location` | `locations` | Reusable address (origin / destination / facility). |
| `Load` | `loads` | Central object. Parties + route + money + status lifecycle. |
| `FreightItem` | `freight_items` | Line item on a load (description, pieces, weight, NMFC class). |
| `BillOfLading` | `bills_of_lading` | Immutable snapshot of a load's parties + freight, rendered to PDF. |

## Load status lifecycle

Guarded in `App\Domain\Loads\LoadStatus` — illegal transitions return **422**.

```
quoted ──▶ booked ──▶ dispatched ──▶ in_transit ──▶ delivered ──▶ invoiced
   │          │            │
   └──────────┴────────────┴──▶ cancelled
```

## Money

Load `customer_rate_cents` (revenue) and `carrier_cost_cents` (cost) are stored
as integer cents to avoid float drift. `margin_cents` is **derived**, never
persisted (`Load::marginCents()`).

## Bill of Lading

A BOL snapshots the customer, carrier, ship-from/ship-to addresses, and freight
at generation time (`BillOfLadingService::generateFromLoad`). PDF rendering runs
on a queued job (`GenerateBillOfLadingPdf`) via **dompdf** — pure PHP, no
headless browser ([ADR 0003](adr/0003-shared-hosting-constraints.md)). The
snapshot means editing the load later never changes an issued document.

## Extension seams (future pillars)

These interfaces exist so later pillars slot in without reshaping the core. Bind
a concrete implementation in `AppServiceProvider::$bindings`.

### `App\Domain\LoadBoard\LoadBoardProvider`

A source of available loads. **Bound today** to `InternalLoadBoardProvider`
(the broker's own loads, org-scoped). External providers implement the same
`search(LoadBoardSearch): Collection<LoadBoardResult>` contract:

- **DAT / Truckstop / 123Loadboard** — partner-gated commercial APIs. No open
  public access; each needs an agreement and credentials. When available, add
  e.g. `DatLoadBoardProvider implements LoadBoardProvider`, map `LoadBoardSearch`
  onto the provider's query params, normalize responses into `LoadBoardResult`,
  and swap the binding. No calling code (`LoadBoardController`) changes.

### `App\Domain\Comms\CommsChannel`

The unified-comms pillar (SMS / voice / email in one inbox). **Interface only —
not implemented in the MVP.** Planned first implementation: a Twilio SMS
channel (`send(to, body): messageId`), then inbound webhooks threading messages
onto `Customer` / `Carrier` contacts.

## Domain skills

When building later pillars, lean on the available domain skills:
`carrier-relationship-management`, `logistics-exception-management`,
`customs-trade-compliance`, `returns-reverse-logistics`.
