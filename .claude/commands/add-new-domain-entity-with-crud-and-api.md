---
name: add-new-domain-entity-with-crud-and-api
description: Workflow command scaffold for add-new-domain-entity-with-crud-and-api in logistics-app.
allowed_tools: ["Bash", "Read", "Write", "Grep", "Glob"]
---

# /add-new-domain-entity-with-crud-and-api

Use this workflow when working on **add-new-domain-entity-with-crud-and-api** in `logistics-app`.

## Goal

Adds a new domain entity (model) with full CRUD API endpoints, validation, resource serialization, database migration, factories, and feature tests.

## Common Files

- `api/app/Models/*.php`
- `api/database/migrations/*_create_*_table.php`
- `api/database/factories/*Factory.php`
- `api/app/Http/Requests/*/*.php`
- `api/app/Http/Resources/*.php`
- `api/app/Http/Controllers/Api/*Controller.php`

## Suggested Sequence

1. Understand the current state and failure mode before editing.
2. Make the smallest coherent change that satisfies the workflow goal.
3. Run the most relevant verification for touched files.
4. Summarize what changed and what still needs review.

## Typical Commit Signals

- Create new Eloquent model in app/Models/
- Create database migration in database/migrations/
- Create factory in database/factories/
- Create Form Request validation classes in app/Http/Requests/[Domain]/
- Create Resource classes in app/Http/Resources/

## Notes

- Treat this as a scaffold, not a hard-coded script.
- Update the command if the workflow evolves materially.