# Changelog

Living record of what's been built, grouped by the spec's build phases. Updated at the end of each phase — entries are amended in place if a later phase changes something an earlier entry described, not just appended to. If you're picking up this project cold, read this top to bottom before touching code.

## Phase 1 — Foundation

Laravel 13.19 + Livewire 4.3 + Filament 5.6 + spatie/laravel-permission 8.3, scaffolded and verified. Filament admin panel at `/admin` with a light theme and teal accent per the spec's UI requirements. MySQL database, admin user seeded.

Deviations from the original spec worth knowing:
- Spec assumed Laravel 11 / Livewire 3 — those are past security support as of mid-2026, so latest-stable was used throughout instead (Laravel 13, Livewire 4, Filament 5).

## Phase 2 — Core CRM: Agents, Companies/Contacts, Leads, Pipeline, Activities/Follow-ups

- **Roles & agents**: `User` model gained spatie roles (`admin`/`agent`/`production`), plus `work_scope` (`lead_gen_only`/`sales_only`/`full_cycle`), `status` (active/inactive), `created_by_admin_id`. New **Agents** resource lets Admin create/edit/deactivate agents and assign work-scope.
- **Companies & Contacts**: full CRUD, one company has many contacts, company profile page shows contacts + lead history.
- **Leads**: full lifecycle model with `LeadActivity` timeline (calls, notes, emails, status changes, reassignments), follow-up date/note fields.
- **Lead Kanban pipeline board** (New→Contacted→Qualified→Quoted→Negotiation→Won→Lost), built on `relaticle/flowforge` (the only Kanban plugin that installs cleanly on Laravel 13/Filament 5 — others pin `illuminate/contracts` to v11/v12). This is the board from spec §3.1, distinct from the separate Deal Board added in Phase 3.
- **Role-scoped visibility**: Admin sees every lead; Agents see only their own assigned leads; `sales_only` agents can't create new leads; Production has zero access to the sales pipeline. Enforced via `LeadPolicy` + scoped `getEloquentQuery()`.
- **Business rules**: `lead_gen_only` agents are blocked from dragging a lead past "Qualified" (spec's hand-off rule); moving to "Lost" requires a reason via a dedicated action, not a bare drag; every status change/reassignment logs a `LeadActivity`; Admin can reassign leads to a different agent.
- **Follow-ups dashboard widget**, scoped by role.

Bugs hit and fixed during this phase:
1. Filament 5 moved the `Get` form-closure type-hint from `Filament\Forms\Get` to `Filament\Schemas\Components\Utilities\Get` — the old namespace throws a `TypeError` only visible in `storage/logs/laravel.log`, not on screen.
2. `relaticle/flowforge` v4.0.13 ships without its compiled CSS and never registers a stylesheet asset — its board rendered as an unstyled vertical stack. Fixed via Filament's official custom-theme mechanism (`resources/css/filament/admin/theme.css`, registered via `->viteTheme()`), which builds our own Tailwind bundle that scans the plugin's Blade views. This is the general fix for any Filament plugin that renders unstyled.

## Phase 3 — Quotations & Products

- **Product Catalog**: `Product` (name, type, material, size options, MOQ, base price) with `ProductPriceTier` line items (quantity breakpoints → unit price), managed via a `Repeater` on the product form. Admin manages the catalog; Agents get read-only visibility (spec §3.5).
- **Deal Board** (spec §3.3 — the sales-pipeline board, separate from the Lead board): a `Deal` is auto-created the moment a Lead's status reaches "Quoted" (hooked into the existing `LeadsBoard::moveCard()` and `EditLead` save, additively — the Lead board itself is untouched). Its own Kanban (Quoted → Negotiation → Won → Lost) via the same Flowforge pattern as the Lead board, with value/expected-close-date/probability on each card and filters by agent/product/value range. Scoped the same way as Leads (Admin sees all, Agent sees own, Production has none).
- **Quotations**: built as a relation manager on a Deal's page — line items pick a Product and auto-fill unit price from whichever price tier matches the entered quantity (reactive, updates live as you type). Versioning via a "Duplicate as new version" action. Status flow Draft → Sent → Approved/Rejected. A configurable discount-approval threshold (Admin-editable at **System → Settings**) blocks "Sent" until an Admin explicitly approves the discount if it exceeds the threshold. PDF download and a 14-day signed share link (no login required) via `barryvdh/laravel-dompdf`.
- **Deal-to-Order conversion**: marking a Deal "Won" creates a minimal `Order` row (status `Pending`) and syncs the parent Lead to "Won". The full order/dispatch *workflow* (status pipeline, Production role UI, dispatch form) is Phase 4 — this phase only lays the data down, per the spec's own phase split ("Deal-to-Order conversion" is explicitly listed under Phase 3, the *pipeline* under Phase 4).
- **System Settings** page (Admin-only): currently just the discount-approval threshold; built to hold more settings as later phases need them (e.g. pipeline stage naming, mentioned in spec §3.11, intentionally deferred — see "Known gaps" below).

Bugs hit and fixed during this phase:
1. Same `Get`-namespace issue as Phase 2, but for `Set`: `Filament\Forms\Set` → `Filament\Schemas\Components\Utilities\Set`. Hit in the quotation line-item repeater's reactive price autofill. Worth grepping for `use Filament\\Forms\\(Get|Set);` whenever a `TypeError` mentions these classes — that's always the fix.
2. **Filament 5 defaults relation managers on a resource's View page to read-only** (`hasReadOnlyRelationManagersOnResourceViewPagesByDefault()`), hiding Create/Edit/Delete and showing only View — silently, with no error. This broke the Quotations relation manager (no "New quotation" button appeared) *and*, it turned out, had been silently affecting Phase 2's Contacts relation manager on a Company's profile page the whole time (adding a contact from a company's page never worked, and nothing exercised that path until now). Fixed both by overriding `isReadOnly(): bool { return false; }` on `QuotationsRelationManager` and `ContactsRelationManager`. **If a relation manager on a `ViewRecord` page is ever missing its Create/Edit buttons with no visible error, this is almost certainly why.**
3. `Deal::firstOrCreate(['lead_id' => ...], [...])` without explicitly setting `stage` in the create array left the in-memory model's `stage` attribute `null` even though the DB row correctly got the column's default value — Eloquent doesn't re-fetch DB-applied defaults after an insert. Harmless where the returned instance wasn't used further, but fixed by passing `'stage' => DealStage::Quoted` explicitly at both call sites so the in-memory object is always trustworthy immediately after creation.

Verification for this phase used real logged-in browser sessions (Playwright + Edge) covering: product creation with a price tier, the Deal board rendering and scoping, building a quotation with a reactive product/quantity/price line item, the discount-approval gate, the PDF route, "Mark as Sent", and the Won→Order conversion — plus a full smoke test loading every nav page as all four roles (Admin, and agents of each work-scope) with zero HTTP 500s or console errors, and a regression pass confirming the Phase 2 Lead board and its seeded data were untouched.

### Known gaps deliberately left for later phases
- Pipeline stage *naming* is not yet configurable (spec §3.11) — stages are currently fixed PHP enums (`LeadStatus`, `DealStage`, `OrderStatus`). Making labels admin-editable would mean moving them into the database, which is a bigger structural change than this phase's scope; flagged here so it isn't forgotten, not silently dropped.
- A company-wide `activity_log` covering *every* admin action (spec §3.11) doesn't exist yet — only Lead-specific actions (status changes, reassignment) are logged, to `lead_activities`. General accountability logging is Phase 7 (Polish) territory.

## Example scenario: a lead's full journey to an order

This walks the exact path the system supports end-to-end, referencing what phase built each step — useful both as a smoke-test script and as onboarding for anyone new to the app.

1. **Admin** logs in at `/admin/login`, goes to **Catalog → Products**, adds "5-ply Corrugated Box" (base price 50/unit) with a price tier: 500+ units at 40/unit. *(Phase 3)*
2. **Admin** goes to **System → Agents**, creates an agent "Fiona Full" with work-scope **Full Cycle**. *(Phase 2)*
3. **Fiona** logs in, goes to **Sales Pipeline → Leads**, clicks **New** on the "New" column, creates a lead for a new contact (inline company/contact creation right in the form) with product interest "Corrugated Boxes", assigns it to herself. *(Phase 2)*
4. Fiona works the lead: logs a call, adds a note, sets a follow-up date — all from the lead's activity timeline. The Dashboard's **Follow-ups due** widget will surface it once that date arrives. *(Phase 2)*
5. Fiona drags the card to **Qualified**, then to **Quoted**. The moment it hits "Quoted", a **Deal** is silently created behind the scenes, owned by Fiona. *(Phase 3)*
6. Fiona goes to **Sales Pipeline → Deals**, finds the new deal card, opens it, clicks **New quotation**, adds the "5-ply Corrugated Box" line item, types quantity 600 — unit price auto-fills to 40 (the 500+ tier) the moment she tabs out. Total and discount recalculate automatically. *(Phase 3)*
7. She clicks **Mark as Sent**. If the discount she'd negotiated exceeded the Admin-set threshold, this would instead tell her to wait for Admin approval — it doesn't here, since there's no discount at list price. *(Phase 3)*
8. The client comes back positive. Fiona (or Admin) uses **Record client response → Approved by client**, then drags the Deal card to **Won**. This creates an **Order** (status Pending) and marks the Lead "Won" too. *(Phase 3 creates the Order row; Phase 4 will add the status pipeline / dispatch workflow on top of it.)*
9. *(Not yet built)* Production picks up the order queue, moves it through In Production → Ready to Dispatch → Dispatched → Delivered, filling in dispatch details. This is Phase 4.

## Test accounts (dev database)

- `admin@crm.local` / `AdminCrm2026!`
- `lea.gen@crm.local` / `AgentPass123!` (Lead Gen only)
- `sam.sales@crm.local` / `AgentPass123!` (Sales only)
- `fiona.full@crm.local` / `AgentPass123!` (Full Cycle) — owns the seeded test lead (Bilal Khan / Acme Packaging Co), its Deal, and quotation v1
