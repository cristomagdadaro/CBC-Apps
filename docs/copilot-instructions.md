# CBC-Apps AI Coding Instructions

## Usage Instructions

### For the Agentic Prompt
1. Open GitHub Copilot Chat in Agent mode.
2. Attach [.github/prompts/agent.refactor-controllers.prompt.md](../.github/prompts/agent.refactor-controllers.prompt.md).
3. Let the agent follow the structured refactoring plan for the target controllers.
4. Start with: "Refactor FormBuilderController to use BaseController standards".

### For Copilot Instructions
The shared instruction file at [.github/copilot-instructions.md](../.github/copilot-instructions.md) applies these defaults to Copilot interactions:
- New controllers extend `BaseController` by default.
- Repository injection uses the `$service` property convention.
- CBC domain patterns for Events, Inventory, and Requests stay consistent.

## Architecture Overview
This is a Laravel 10 application using Inertia.js with Vue 3 for a modern SPA experience. It manages event registrations, inventory tracking, and resource requests for an organization (CBC).

**Key Components:**
- **Events/Forms**: Registration system with subforms (preregistration, feedback, post-test) using `Form`, `Participant`, `Registration` models
- **Inventory**: Item management with transactions, suppliers, categories using `Item`, `Transaction`, `Supplier`, `Category` models  
- **Requests**: Lab/equipment usage requests using `UseRequestForm`, `RequestFormPivot` models

**Tech Stack:**
- Backend: Laravel 10, PHP 8.1+, MySQL
- Frontend: Vue 3 + Inertia.js, Tailwind CSS, Vite
- Auth: Laravel Jetstream + Fortify
- PDFs: DomPDF for form generation
- Barcodes/QR: jsbarcode, qrcode.vue, @zxing/browser

## Development Patterns

### Model Conventions
- Extend `BaseModel` for all domain models
- Define `$searchable` array for global search functionality
- Use UUIDs (`HasUuids`) for primary keys where needed (e.g., `Item`, `Transaction`)
- Soft deletes on inventory models
- Custom date serialization: `Y-m-d` format via `BaseModel::serializeDate()`

**Example:**
```php
class Item extends BaseModel {
    use HasFactory, SoftDeletes, HasUuids;
    
    protected array $searchable = ['name', 'brand', 'description'];
    
    protected $casts = ['id' => 'string'];
}
```

### Repository Pattern
- All data access through repositories extending `AbstractRepoService`
- Controllers extend `BaseController` and inject repository
- Standard CRUD: `create()`, `update()`, `delete()`, `search()`

**Example:**
```php
class ItemController extends BaseController {
    public function __construct(ItemRepo $repository) {
        $this->service = $repository;
    }
}
```

### Request Validation
- Custom request classes in `App\Http\Requests`
- Use config files for complex validation rules (e.g., `config/subformtypes.php` for form field validation)

### Enums
- Use PHP 8.1 enums for type safety
- Located in `App\Enums`: `Subform`, `Inventory`, `Sex`

### Observers
- `RequestFormPivotObserver` purges cached PDFs on model changes
- Register in `AppServiceProvider`

## Key Workflows

### Form Management
- Events have forms with subforms (preregistration → registration → feedback)
- Automatic expiration via `php artisan forms:update-expired` command
- PDF generation via `PDFGeneratorController`

### Inventory Transactions
- Track item movements with `Transaction` model
- Types: incoming/outgoing via `transac_type` field
- Barcode scanning support

### Request System
- Users request lab/equipment use via forms
- Pivot table `request_form_pivot` links requests to items
- Cached PDFs in `public/generated-pdfs/` by template directory

## Build & Development

### Frontend Build
```bash
npm run dev    # Development with hot reload
npm run build  # Production build
```

### Testing
- Use Pest/PHPUnit
- Run with `./vendor/bin/pest` or `php artisan test`

### Key Commands
- `php artisan forms:update-expired` - Mark expired event forms
- PDF caching cleared automatically via observers

## Integration Points

### External Libraries
- **DomPDF**: PDF generation (`barryvdh/laravel-dompdf`)
- **Ziggy**: JS routing helper for Vue components
- **Tagify**: Tag input component (`@yaireo/tagify`)
- **Chart.js**: Data visualization
- **ZXing**: Barcode/QR scanning (`@zxing/browser`, `vue-qrcode-reader`)

### File Structure Notes
- Views in `resources/views/` (minimal, mostly Inertia)
- Vue components in `resources/js/`
- Custom configs: `searching.php`, `subformtypes.php`
- Public assets: `public/build/` (Vite output), `public/generated-pdfs/`

## Common Patterns

### Searching & Pagination
- Use `AbstractRepoService::search()` with request params
- Supports: `page`, `per_page`, `sort`, `order`, `search`, `filter`
- Configured in `config/searching.php`

### UUID Handling
- Models with UUIDs disable auto-increment: `$incrementing = false; $keyType = 'string';`
- Cast ID as string in `$casts`

### Date/Time Formatting
- Consistent date format: `Y-m-d`
- Time format: `h:i A` (12-hour)

When adding new features, follow these patterns to maintain consistency.