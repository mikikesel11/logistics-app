```markdown
# logistics-app Development Patterns

> Auto-generated skill from repository analysis

## Overview

This skill teaches the core development patterns and conventions used in the `logistics-app` repository, a JavaScript-based logistics application. The codebase follows clear conventions for file naming, import/export style, and commit messages, and employs structured workflows for adding domain entities, services, database tables, and feature tests. This guide will help you contribute effectively and consistently.

## Coding Conventions

- **File Naming:**  
  Use PascalCase for file names.  
  _Example:_  
  ```
  CustomerController.js
  BillOfLadingService.js
  ```

- **Import Style:**  
  Use absolute imports.  
  _Example:_  
  ```js
  import CustomerService from 'services/CustomerService';
  ```

- **Export Style:**  
  Use default exports.  
  _Example:_  
  ```js
  export default class CustomerController { ... }
  ```

- **Commit Messages:**  
  Use [Conventional Commits](https://www.conventionalcommits.org/).  
  - Prefix: `feat`  
  - Example:  
    ```
    feat: add CRUD endpoints for Carrier entity
    ```

## Workflows

### Add New Domain Entity with CRUD and API
**Trigger:** When introducing a new business object (e.g., Customer, Carrier, Load, BillOfLading) with API CRUD operations  
**Command:** `/new-entity`

1. **Create a new model** in `app/Models/`.
2. **Create a migration** in `database/migrations/`.
3. **Create a factory** in `database/factories/`.
4. **Add Form Request validation** in `app/Http/Requests/[Domain]/`.
5. **Create a Resource class** in `app/Http/Resources/`.
6. **Create a Controller** in `app/Http/Controllers/Api/`.
7. **Register CRUD endpoints** in `routes/api.php`.
8. **Write feature tests** in `tests/Feature/`.
9. *(Optional)* Add repository/service layer and DTOs in `app/Domain/[Domain]/`.

_Example:_  
```js
// app/Models/Carrier.js
export default class Carrier { ... }
```
```js
// app/Http/Controllers/Api/CarrierController.js
export default class CarrierController {
  // CRUD methods
}
```

### Add Feature Service Layer with Tests
**Trigger:** When encapsulating business logic or external integration behind a service/provider  
**Command:** `/new-service`

1. **Create a service/provider class** in `app/Domain/[Domain]/`.
2. **Define supporting data objects** (DTOs, enums, exceptions) in `app/Domain/[Domain]/`.
3. *(If needed)* Add interface and implementation classes for repository/provider seams.
4. **Write or update feature tests** in `tests/Feature/` to cover the new service's behavior.

_Example:_  
```js
// app/Domain/Load/LoadService.js
export default class LoadService { ... }
```

### Add Database Table with Migration and Factory
**Trigger:** When persisting a new kind of data in the database  
**Command:** `/new-table`

1. **Create a migration file** in `database/migrations/`.
2. **Create a model** in `app/Models/`.
3. **Create a factory** in `database/factories/`.
4. *(Optional)* Update seeders or add feature tests.

_Example:_  
```js
// database/migrations/2024_01_01_create_shipments_table.js
// Migration code here
```

### Add Feature Test for API Endpoint
**Trigger:** When adding or modifying an API endpoint and ensuring it is tested  
**Command:** `/new-feature-test`

1. **Create or update a test class** in `tests/Feature/`.
2. **Write tests** for all CRUD operations, validation, and edge cases.
3. **Run tests** to verify behavior.

_Example:_  
```js
// tests/Feature/CarrierApi.test.ts
describe('Carrier API', () => {
  it('creates a carrier', async () => {
    // test implementation
  });
});
```

## Testing Patterns

- **Test File Pattern:** `*.test.ts`
- **Location:** `tests/Feature/`
- **Framework:** Unknown (likely Jest or similar for `.ts` files)
- **Coverage:** CRUD operations, validation, edge cases for API endpoints

_Example:_  
```ts
// tests/Feature/CustomerApi.test.ts
describe('Customer API', () => {
  it('should create a customer', async () => {
    // test logic
  });
});
```

## Commands

| Command            | Purpose                                                           |
|--------------------|-------------------------------------------------------------------|
| /new-entity        | Add a new domain entity with CRUD API, validation, and tests      |
| /new-service       | Add a new service/provider class with supporting tests            |
| /new-table         | Add a new database table with migration and factory               |
| /new-feature-test  | Add or update a feature test for an API endpoint                  |
```