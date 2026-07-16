# ADR 0002 — Single-tenant behavior now, multi-tenant-ready schema

- **Status**: Accepted
- **Date**: 2026-07-16

## Context

The app starts as the tool for one brokerage but is intended to grow into a
SaaS that many brokerages can sign up for. Retrofitting tenancy onto a
single-tenant schema later is expensive and error-prone (every table, query,
and unique constraint is affected).

## Decision

Adopt a **shared-database, shared-schema** multi-tenancy model from day one, but
run it with a single seeded tenant for now:

- Every tenant-scoped table carries an `organization_id` foreign key.
- A global Eloquent scope filters all queries by the current organization.
- A single `Organization` is seeded; the current org is resolved from the
  authenticated user.
- Unique constraints are scoped per-organization (e.g. `unique(organization_id,
  mc_number)`), not globally.

No per-tenant billing, signup, or tenant-admin UI is built yet — only the data
model and query scoping needed to avoid a rewrite.

## Consequences

- Adding real multi-tenant signup later is additive, not a migration of every
  table.
- Every new model must remember `organization_id` + the global scope; enforced
  via a shared base model / trait and covered by org-isolation tests.
- Cross-tenant data leaks are a security risk class we test for explicitly.
