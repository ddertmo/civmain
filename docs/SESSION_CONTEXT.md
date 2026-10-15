# Session status

Short, live handoff. **Read only on a brand-new session** (see `AGENTS.md`).
If the session already has history, ignore this file — the existing
conversation is the context.

- Detailed, portable project notes → [`PROJECT_CONTEXT.md`](PROJECT_CONTEXT.md)
- What changed, per change → [`changes/README.md`](changes/README.md)

Keep the "Recent sessions" list to a few lines. It is not a second changelog.

---

## Recent sessions (fixes / tasks)

- **2026-10-15** — 048: citizen registration captures a full home address
  (Street/Area, Purok, City, Province) — Flutter form + migration + register API
  + reporter panel return the fields.
- **2026-10-15** — 049: Digos City barangay list unified to the official 26
  everywhere — app registration, report form, backend validation, and the admin
  filters (monitoring / pending / resolved / assign-office / Blade).
- **2026-10-15** — 050: session start reworked so a **fresh** session continues
  from this file while an **existing** session keeps its own history; detailed
  notes moved to `PROJECT_CONTEXT.md`.

## Next up (open items)

- Wire the CEO/CENRO `offices/*/report-details.html` prototypes to the real API
  (reporter info + `POST /api/admin/citizen-reports/{id}/priority`).
- Surface citizen street/purok/city/province in the admin/office reporter panels
  (the API already returns them; the UI shows only Home Barangay).
- Optional: `graphify label` needs an LLM key, so community names drift until one
  is set.
