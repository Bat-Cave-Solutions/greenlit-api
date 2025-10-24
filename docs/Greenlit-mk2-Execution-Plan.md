# Greenlit mk2 — Execution Plan (GitHub Issues version)

Purpose
Use this as a copy-paste source for GitHub Projects configuration (views, fields, filters), epics, and issue cards. It’s ordered so you can start coding today. Assumes Laravel + PostgreSQL + Redis + Docker + GitHub Actions, React + Vite + MUI, and remains framework-agnostic where possible.

Project setup (GitHub Projects)
- Project: Greenlit mk2 (table view recommended)
- Custom fields (single select):
  - Epic: Foundations, EF Service, Calculator, Queues & Caching, Reporting & UI, Auth & Users, Deployment & Ops, Migration & QA, Commute Service, Governance & Docs
  - Priority: P0, P1, P2
  - Size: S (≤0.5d), M (1d), L (2–3d), XL (4d+)
- Auto-add: issues and PRs from all Greenlit repos
- Suggested views (replace Trello lists):
  - Backlog: is:open sort:priority asc,created-asc
  - Next Up (This Week): is:open Priority in [P0,P1] sort:priority asc,updated-desc
  - In Progress — batcaves: is:open assignee:batcaves
  - In Progress — Dev B: is:open assignee:@dev-b
  - Review/QA: is:open is:pr review:required OR label:"qa"
  - Blocked: is:open label:"blocked"
  - Done: is:closed sort:closed-desc

Labels (optional, if you prefer labels over fields)
- epic/foundations, epic/ef-service, epic/calculator, epic/queues-caching, epic/reporting-ui, epic/auth-users, epic/deploy-ops, epic/migration-qa, epic/commute, governance-docs
- priority/p0, priority/p1, priority/p2
- size/s, size/m, size/l, size/xl
- blocked, qa

High-level Objectives
- Preserve legacy correctness and auditability (parity with IdentifyFactor behavior)
- Dramatically improve performance (5k+ record productions)
- Standardize deployment, error handling, workers, and auth
- Keep tech choices portable (Docker, Postgres, Redis, Soketi)
- No integration phase: old is turned off when new is ready; cutover when parity achieved

Constraints and Assumptions
- EF selection must support audit date vs record date vs latest modes
- Country → source priority per country; annual_update_month per source
- Climatiq is primary source; cache locally; never overwrite prior-year rows
- calculation_version pinned per production; EF snapshots immutable
- Commute can be separated (defer OK)
- Replace DataTables with MUI DataGrid; reactive updates via WebSockets

Definition of Done (global)
- Passing CI (lint, tests, static analysis), and CodeQL where applicable
- Documentation updated (README/ADR/schemas)
- Observability hooks in place (structured logs, error capture)
- Backwards compatibility: historical results preserved for existing productions
- Security: secrets in env/CI, least-privileged tokens, no plaintext secrets in code

Epics and Issue Cards
Note: Convert each “Card” into a GitHub issue using your Feature request template and set Epic/Priority/Size fields.

Epic: Foundations (P0)
Card: Initialize API + Docker baseline
Description: Scaffold API project, Docker dev stack (app, Postgres, Redis, Soketi, Nginx), health endpoints.
Checklist:
- Docker Compose with named volumes, healthchecks
- Base Laravel project with .env.example
- Postgres + Redis connectivity verified
- Soketi/Socket.io baseline running
Acceptance:
- docker compose up starts stack cleanly
- /health returns 200 with build sha
Fields: Epic=Foundations, Priority=P0, Size=M
Owner: Dev B

Card: Core PostgreSQL schema v1 (emissions hybrid model)
Description: Create emissions table with hybrid model and indexes; add supporting tables for productions, emission_factors, emission_factor_versions, activity_code_tree.
Checklist:
- emissions: relational columns + JSONB data + CHECK guardrails
- Indexes: GIN on data and core btree indexes
- emission_factors + emission_factor_versions with uniqueness constraints
- activity_code_tree skeleton
- Migrations + rollbacks
Acceptance:
- Migration up/down clean; EXPLAIN shows index usage on common queries
Fields: Epic=Foundations, Priority=P0, Size=L
Owner: batcaves

Card: CI hardening baseline
Description: GitHub Actions with test, lint, static analysis, build image, cache deps.
Checklist:
- PHP tests + coverage; Laravel Pint
- Larastan/Psalm
- Composer & npm cache
- Action pins to SHAs
Acceptance:
- PRs require all checks; coverage artifact visible
Fields: Epic=Foundations, Priority=P1, Size=M
Owner: Dev B

Epic: Auth & Users (P1)
Card: Auth scaffolding (Sanctum SPA session; OIDC stubs later)
Description: Implement Sanctum SPA session auth (cookie + CSRF) with scopes model; keep OIDC hooks stubbed for later Google/MS.
Checklist:
- Sanctum SPA session flow + CSRF cookie
- scopes replace bespoke fn_* flags
- org/project membership tables and policies
Acceptance:
- Authenticated session enforces scoped access to protected endpoints
Fields: Epic=Auth & Users, Priority=P1, Size=L
Owner: batcaves

Epic: EF Service (P0)
Card: EF selection spec and parity tests (legacy → new)
Description: Formalize rules (audit > record > latest; annual_update_month; country→source priority; fallback) and write executable tests using legacy fixtures/expected outputs.
Checklist:
- Extract 10–15 representative EF scenarios from old code/DB
- Snapshots of expected factor IDs/years per scenario
- Unit tests for selection covering ordering, fallbacks, ties
Acceptance:
- All scenarios green; matches legacy IdentifyFactor
Fields: Epic=EF Service, Priority=P0, Size=M
Owner: batcaves

Card: EF tables and version locking
Description: Implement EF tables with prefixes (GL:, CUST:), immutable version rows, and calculation_version pinning.
Checklist:
- unique(activity_code, source, year, country nulls not distinct)
- emission_factor_versions keyed by activity_code, source, year, country, released_at
- production.calculation_version stamped at creation
Acceptance:
- Updates create new version rows; old productions unaffected
Fields: Epic=EF Service, Priority=P0, Size=M
Owner: Dev B

Card: Climatiq fetch-on-miss cache adapter
Description: On EF miss, call Climatiq once, normalize, store, return; dedupe by fingerprint; backoff and circuit-breaker.
Checklist:
- Fingerprint: activity_code + source + year + country
- Local cache write; never overwrite prior-year entries
- Retry with exponential backoff + honor 429/Retry-After
Acceptance:
- Cache hit rate visible; no repeated external calls for same fingerprint
Fields: Epic=EF Service, Priority=P1, Size=M
Owner: Dev B

Card: Legacy code → activity_code mapper
Description: Map old factor identifiers to standardized activity_code using MFE/DESNZ IDs; mark GL:/CUST: as needed.
Checklist:
- Mapping table and scripts
- Report of unmapped codes for manual review
Acceptance:
- 95%+ legacy codes mapped automatically; remainder listed
Fields: Epic=EF Service, Priority=P1, Size=M
Owner: batcaves

Epic: Calculator (P0)
Card: Calculation pipeline skeleton
Description: Given a record payload, resolve EF, perform normalization/unit conversion, compute co2e/co2/ch4/n2o.
Checklist:
- Pure function(s) + injectable EF resolver
- Rounding rules configurable
- Persist emission_factor_id and calculation_version
Acceptance:
- Unit tests for multiple categories; deterministic outputs
Fields: Epic=Calculator, Priority=P0, Size=M
Owner: batcaves

Card: Category payload schemas and validation
Description: JSON schema files per category; server-side validation; DB CHECKs for critical invariants.
Checklist:
- Flight, waste, accommodation initial schemas
- CI validation step
- Generated columns for high-usage keys
Acceptance:
- Invalid payloads rejected with clear errors; promoted keys indexed
Fields: Epic=Calculator, Priority=P1, Size=M
Owner: Dev B

Epic: Queues & Caching (P0)
Card: Queue-based recalculation with delta strategy
Description: On changes (record/factor/settings), enqueue jobs; update aggregates using delta method.
Checklist:
- Job payloads include old/new snapshots
- Idempotency keys; retry/backoff
- Aggregates by scope/period/department
Acceptance:
- 5k-record production recalcs complete quickly; no full recompute on small changes
Fields: Epic=Queues & Caching, Priority=P0, Size=L
Owner: Dev B

Card: Real-time progress and completion via WebSockets
Description: Push job progress and “calculation complete” events; client subscribes per project.
Checklist:
- Soketi channels and auth
- Progress percent and summary payloads
Acceptance:
- UI receives updates without refresh
Fields: Epic=Queues & Caching, Priority=P1, Size=M
Owner: batcaves

Epic: Reporting & UI (P1)
Card: Replace DataTables with MUI DataGrid
Description: Implement DataGrid for emissions and factors; server-side pagination, filters, column-level search.
Checklist:
- Virtualization; loading states
- Column pinning, export to CSV/PDF endpoints
Acceptance:
- Lists are responsive with 5k+ rows
Fields: Epic=Reporting & UI, Priority=P1, Size=L
Owner: Dev B

Card: Cached dashboards under 200ms
Description: Read precomputed aggregates; cache invalidated by jobs; chart-ready endpoints.
Acceptance:
- P95 < 200ms for key dashboards on seeded 10k data
Fields: Epic=Reporting & UI, Priority=P1, Size=M
Owner: batcaves

Epic: Deployment & Ops (P0)
Card: Release workflow and zero-downtime migrations
Description: GH Actions builds/pushes image; run DB migrations; health checks; rollback strategy.
Checklist:
- GHCR publish; environment promotion gates
- Migration safety (online migration patterns)
Acceptance:
- One-click deploy; rollback documented/tested
Fields: Epic=Deployment & Ops, Priority=P0, Size=M
Owner: Dev B

Card: Observability and error handling
Description: Structured logs, request IDs, error boundaries; Sentry/Bugsnag integration (optional).
Acceptance:
- Errors logged with context; correlation IDs across worker and API
Fields: Epic=Deployment & Ops, Priority=P1, Size=M
Owner: batcaves

Epic: Migration & QA (P0 toward cutover)
Card: MySQL → Postgres migration scripts
Description: Export old data; transform schemas; import; integrity checks.
Acceptance:
- Dry-run migration completes; row counts and checksums match
Fields: Epic=Migration & QA, Priority=P0, Size=L
Owner: Dev B

Card: Parity test suite (totals, factors, backdating)
Description: Snapshot expected totals and EF selections for sample productions; compare new vs old.
Acceptance:
- 100% parity on golden datasets or documented exceptions
Fields: Epic=Migration & QA, Priority=P0, Size=M
Owner: batcaves

Card: Cutover playbook (no integration phase)
Description: Single downtime window; DNS/app switch; rollback plan; comms.
Acceptance:
- Playbook reviewed; dry-run executed in staging
Fields: Epic=Migration & QA, Priority=P0, Size=S
Owner: Dev B

Epic: Commute Service (P2 or deferred)
Card: Commute microservice skeleton
Description: Separate DB and API; summarization algorithms; webhook sync to emissions.
Acceptance:
- Independent deploy; back-pressure safe; tool_locked semantics
Fields: Epic=Commute Service, Priority=P2, Size=L
Owner: Dev B

Epic: Governance & Docs (P1)
Card: SECURITY.md, CONTRIBUTING.md, CODEOWNERS, PR/Issue templates
Acceptance:
- Repo governance standardized; branch protections defined
Fields: Epic=Governance & Docs, Priority=P1, Size=S
Owner: batcaves

Card: JSON key dictionary and promotion policy
Acceptance:
- Living doc exists; CI lints payload keys
Fields: Epic=Governance & Docs, Priority=P1, Size=S
Owner: Dev B

Open Questions (track as a GitHub issue with checklist)
- [ ] Approve factor code prefixes: CUST:, GL:
- [ ] Country → source priority matrix per active country
- [ ] annual_update_month per source (MFE, DESNZ)
- [ ] List of legacy factors not present in Climatiq
- [ ] Minimum viable unit conversions per activity_code
- [ ] calculation_version naming (date-based vs semver)
- [ ] Target report set and display/rounding rules
- [ ] Commute split now vs later (defer OK)

Today’s Plan (suggested)
batcaves:
- Start “Core PostgreSQL schema v1 (emissions hybrid model)” — focus migrations + indexes + guardrails. Target PR by EOD.
- Kick off “EF selection spec and parity tests” — set up 5 golden scenarios and fixture harness.

Dev B:
- Start “Initialize API + Docker baseline” — compose stack + health endpoint.
- If time remains: “CI hardening baseline” — basic test/lint workflow.

Two-week Cutline (P0 focus)
- Finish Foundations: Docker, schema v1, CI basics
- EF Service: parity tests, EF tables/versioning, calc_version pinning
- Calculator: pipeline skeleton
- Queues & Caching: job scaffolding + delta strategy
- Deployment: release workflow + zero-downtime migrations
- Migration & QA: draft migration scripts + parity suite harness

Acceptance Criteria References
EF selection parity:
- Given audit date mode and country priority chain, selector returns same (activity_code, source, year) as legacy for all scenarios.
- Fallback to Climatiq cache only when no local match; cached thereafter.

Calculation pipeline:
- Deterministic outputs for co2e/co2/ch4/n2o with rounding rules; persists emission_factor_id and calculation_version per record.

Delta recalc:
- Aggregate updates are computed from old→new difference; no full-table recompute; idempotent job retries.
