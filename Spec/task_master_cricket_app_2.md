# Cricket - Task Master Specification v6.0

## Metadata

| Field | Value |
|-------|-------|
| Date | 2025-10-28 |
| Owner | Kavin |
| App | Cricket - Task Master |

---

# 1. Project Overview v6.0

Consolidated Task Master v5 + recent technical decisions: schema hardening, conflict resolution, cron cadence, DB pooling, job locking, security/roles, POTM normalization, POTS spec, SSE fallback, match state machine, clone rules and common-player handling. Canonical source for Cursor and devs.

## 1.1. Goals & Constraints

Primary goal: reliable, auditable scoring app on shared hosting (LAMP). Non-goals: heavy realtime WebSockets on shared host. Constraints: limited DB connections, no persistent background workers assumed.

## 1.2. Deliverables v6

PWA scorer, PHP ingestion endpoints, MySQL schema with FKs & indexes, POTM & POTS engines, admin dashboard, runbook, monitoring and test suites.

# 2. Data Model & Schema Hardening

Explicit tables, FKs, indexes, cascade rules, backfill strategy and migration plan.

## 2.1. Teams & Series

teams table, series table; matches.series_id FK; index on matches.series_id

## 2.2. Player Appearances (canonical)

player_appearances table: appearance_id → player_id, match_id, team_id; enforced FK relationships; unique(player,match,team)

## 2.3. Events table (append-only)

events: numeric PK + event_uuid (CHAR(36) unique), match_id FK, appearance_id FK, payload_json (short keys), created_at, processed_flag; indexes on (match_id,created_at) and (appearance_id).

## 2.4. Stats Cache & last_event_at

stats_cache contains precomputed aggregates per appearance and per player; add last_event_at DATETIME to indicate freshness; index last_event_at.

## 2.5. POTM / POTS tables

potm_decisions, pots_aggregate, pots_overrides, impact_events for audit and bonus computation.

## 2.6. Audit & Edit Tables

player_edits table (edit history), admin_action_logs, clone_links, events_suspense for orphaned events.

## 2.7. Index Strategy

hot-read indexes: events.appearance_id, events.match_id+created_at, stats_cache.appearance_id, stats_cache.player_id+match_id, matches.series_id. Keep indexes minimal to reduce write overhead.

## 2.8. FK Policies & Cascade Rules

Prefer RESTRICT for players/teams to avoid accidental mass-deletes; CASCADE for match->appearances if desired but default to soft-delete pattern; document decisions in schema_policy.md.

# 3. Conflict Resolution & Multi-device Scoring

Sequence-based optimistic concurrency, idempotency, soft locks, conflict markers, reconcile UI and server diff flow.

## 3.1. Event model fields

event_uuid, client_id, client_ts, client_base_seq, assigned_server_seq (on insert), appearance_id, ball_index, payload.

## 3.2. Server seq / conflict flow

matches.last_seq maintained; POST /api/v4/events includes client_base_seq; if stale return 409 + diff; client merges or presents conflict UI.

## 3.3. Soft-locks for high-impact ops

match_locks table for actions: finalize_over, finalize_match, substitute; TTL-based locks to avoid deadlocks.

## 3.4. Conflict marking & admin reconciliation

conflicting events flagged with conflict_flag; admin panel shows both variants and allows authoritative selection with reason logged.

# 4. Cron Cadence, Job Control & Recompute Safety

Recommended cron frequencies and safe job patterns for shared hosting.

## 4.1. Frequencies (recommended)

Immediate write for events; incremental stats recompute: every 30s (aim) or 60s if provider limits; series aggregates: every 5 minutes; nightly full reindex.

## 4.2. Job table & heartbeats

jobs table with cursor JSON, last_heartbeat, status. Use GET_LOCK or job claim to prevent overlap; heartbeat every 10s.

## 4.3. Chunked recompute & resumability

Split recompute into small chunks (per-appearance or batch of N appearances) with progress saved in job.cursor so long-running jobs resume rather than restart.

## 4.4. If recompute > cron interval

cron will see running job (fresh heartbeat) and skip; if heartbeat stale reclaim; resume from cursor on next run.

# 5. DB Connection & Timeouts (shared-hosting)

Persistent PDO connections, limited concurrency, semaphores for heavy jobs, per-query timeouts, batch inserts to reduce connections.

## 5.1. PDO config

ATTR_PERSISTENT=true, ATTR_TIMEOUT small (3-5s), ERRMODE_EXCEPTION.

## 5.2. Connection gate for recompute

Use semaphore table or GET_LOCK for long-running recompute to limit parallel DB usage (max 1-2 concurrent recomputes).

## 5.3. Query time budget

Use chunked queries and set SESSION MAX_EXECUTION_TIME where available; detect slow-query and bail to next chunk.

# 6. Security, Auth & RBAC

JWT-based auth, role model, middleware checks, audit logs, rate limiting, input validation and sanitization.

## 6.1. Roles and permissions

super_admin, admin, senior_scorer, scorer, viewer. Define who can clone, override POTM/POTS, edit players mid-match.

## 6.2. Auth implementation

JWT tokens with role claims; endpoints validate role; admin actions require reason and audit log.

## 6.3. Rate limiting

Per-token/IP token-bucket stored in DB; reject with 429 and Retry-After; higher quota for authorized scorer tokens.

## 6.4. Input validation

JSON Schema validation for events + business rules check: no negative runs, appearance_id belongs to match, ball boundaries, max payload size.

## 6.5. SQL injection & XSS prevention

Prepared statements, length limits, HTML-encode UI text, content security policies and HTTPS enforcement.

# 7. POTM Normalization (explicit)

Canonical formula with overs_norm=6.0; normalized runs and wickets per over; impact bonuses; bowling econ transform; weights configurable.

## 7.1. POTM formula

norm_runs=(R/O)*overs_norm; norm_wk=(W/O)*overs_norm; POTM_score = wR*norm_runs + wW*norm_wk + wF*fielding + impact_bonus; clamp O >= 1/6

## 7.2. Impact events

impact_events table to store match_defining events and their impact_value used in POTM calculation.

# 8. POTS — Player of the Series (full)

Aggregate potm_points + normalized performance + consistency bonus. Configurable weights and tie-breakers. Precompute into pots_aggregate for fast reads.

## 8.1. POTS computation steps

A: compute per-appearance performance_score; B: aggregate per-player in series window; C: apply consistency bonus; D: final weighted sum; E: rank + tie-breakers.

## 8.2. POTS table and overrides

pots_aggregate, pots_overrides; admin override with audit; incremental recompute on match completion.

# 9. Clone Match Rules & Edge Cases

Default clean clone (no events), admin flag to include edits, create new appearances per team, traceability via clone_links; cross-team player handled by new appearance rows.

## 9.1. Clone options UI

include_edits (admin), copy_subs, copy_jersey_numbers, toss_override (required)

# 10. Common Player Handling

Appearance-level attribution with player_appearances; events reference appearance_id; aggregation for POTS by player_id across appearances.

## 10.1. Assigning same player to both teams

System warns and auto-creates separate appearance rows; events attach to correct appearance_id; leaderboards can aggregate by player_id.

# 11. Realtime: Polling vs SSE strategy

Adaptive polling fallback by default; optional SSE gateway on small VPS for push where available; scorer writes local optimistic UI and relies on ACKs.

## 11.1. SSE gateway pattern

Shared-hosting backend posts diffs to lightweight SSE gateway; gateway holds long connections; reduces polling load on shared host.

# 12. Match State Machine (JSON)

draft → scheduled → live → completed | abandoned | cancelled; guards and actions defined; use match_locks for guarded transitions.

## 12.1. Over & Innings rules

Legal vs illegal deliveries; increment ball on legal; over end on 6 legal balls; bowler per over enforced; mid-over bowler change only for injury with admin approval.

## 12.2. Innings transitions

Auto-start second innings if auto_start flag set and innings1 completed; otherwise admin starts innings2; detect chase finish to auto-finalize match.

# 13. Stats staleness detection & UI

stats_cache.last_event_at updated on event ingest for freshness badge; immediate delta updates + scheduled consistency recompute to fix drift.

# 14. Testing & QA additions

Add tests for conflict seq flow (409 diff), appearance aggregation for cross-team players, POTM/POTS normalization unit tests, recompute chunking & resume tests, rate-limit tests.

# 15. Monitoring & Alerts

ingestion error rate, conflict frequency, job runtime, DB connections, slow queries, rate-limit rejections; set thresholds and alerting channels.

# 16. Migration & Rollout Plan (high level)

Stage A: create tables/indexes nullable fields; Stage B: backfill appearance_id for legacy events and populate stats_cache; Stage C: enable FKs and NOT NULL with low-traffic window. Always DB dump + rollback scripts.

# 17. Operational Runbook Snippets

How to handle orphan events, reclaim stale locks, manual recompute steps, revert a clone, and process pots_overrides.

# 18. Open Decisions (require product sign-off)

1) Default overs_norm for normalization (suggest 6). 2) Default POTM base points. 3) Edit window duration for scorer (suggest 15 minutes). 4) Cron minimum frequency allowed by host (30s vs 60s).

# 19. Next immediate tasks (actionable backlog)

1) Implement server_seq + 409-diff flow. 2) Add stats_cache.last_event_at and incremental touch on ingest. 3) Add job table + heartbeat and chunked recompute worker. 4) Add rate-limiter table + middleware. 5) Implement RBAC & audit for admin actions. 6) Create SSE gateway plan if needed.

