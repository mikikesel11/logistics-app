# ADR 0001 — API-first Laravel backend with a React/TS web client

- **Status**: Accepted
- **Date**: 2026-07-16

## Context

The product is a freight brokerage operating system that must run on the web
now and be portable to native iOS/Android apps later. The owner prefers PHP.

A common misconception is that a PHP app can be "ported" to a native phone app.
It cannot — PHP runs on a server. The portable, shareable thing across web and
native clients is an **HTTP API**.

## Decision

Build the backend as an **API-first Laravel application** that exposes a JSON
API. Build the web front end as a **decoupled React + TypeScript SPA** that
consumes that API. Future native clients (likely React Native) consume the same
API.

Authentication uses **Laravel Sanctum**, which supports both SPA cookie auth
(web) and token auth (native) against a single guard.

## Consequences

- One backend contract serves web and future native clients — no logic
  duplicated per platform.
- The web app is a static bundle (`vite build`) and needs no Node runtime on the
  host, which suits shared hosting (see ADR 0003).
- Slightly more upfront wiring than a monolithic Blade app (separate SPA, CORS,
  token/cookie handling), accepted for the multi-client payoff.
- Choosing React (over Vue/Inertia) keeps React Native a natural later step and
  matches the team's TypeScript-first standards.
