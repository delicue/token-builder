---
description: "Build the MTG token/card creator feature: blank canvas, card fields, presets, tapped/counter state"
agent: "agent"
---
Implement a Magic: The Gathering token/card creator feature for this app's dashboard.

Follow the `build-feature-with-tests` skill: implement the feature, write a Pest test, and verify the UI (browser tools, falling back to Playwright if needed).

## Feature Spec

A workspace where the user can create and manage MTG-style token cards:

- **Blank canvas**: an empty play area that starts with no cards; the user adds cards to it.
- **Card fields**:
  - `name` (string)
  - `type` (string, e.g. "Creature — Zombie")
  - `power` (numeric)
  - `toughness` (numeric)
  - `counters` (numeric — number of +1/+1 or -1/-1 counters currently on the card, applied on top of base power/toughness)
  - `tapped` (boolean)
- **Presets**: a set of preselectable token templates (e.g. common MTG tokens like "1/1 Soldier", "2/2 Zombie") that can be added to the canvas with one click, pre-filled with their name/type/power/toughness.
- **Custom cards**: the user can also fill in a blank form to create a card with arbitrary values instead of using a preset.
- **In-play actions**: each card on the canvas can be:
  - Tapped/untapped (toggle, reflected visually — e.g. rotated)
  - Adjusted via counters (increment/decrement, affecting displayed power/toughness)
  - Removed from play (delete from the canvas)
- Cards can be added at any time, in any quantity, and removed independently of one another.

## Constraints
- Reuse the existing Livewire/Flux/Alpine conventions already used on the dashboard (see [dashboard.blade.php](../../resources/views/dashboard.blade.php)).
- Keep it scoped to the authenticated dashboard area; no new auth logic needed.
- No persistence is required unless requested — in-memory/session state for the current view is sufficient.
