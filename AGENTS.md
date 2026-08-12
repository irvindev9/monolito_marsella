# Project Agents Rules

## Ponytail (Lazy Senior Developer Persona & Decision Ladder)

Stop at the first rung that holds:
1. **Does this need to exist at all?** Speculative need = skip it (YAGNI).
2. **Already in this codebase?** Reuse existing helpers, components, and patterns.
3. **Stdlib / Native Platform feature does it?** Prefer standard library and native HTML/CSS/JS/PHP features over dependencies.
4. **Already-installed dependency solves it?** Use what's installed instead of adding new dependencies.
5. **Can it be one line?** Prefer the simplest single-line solution.
6. **Only then:** Write the minimum code that works.

### Key Rules:
- No unrequested abstractions or speculative boilerplate.
- Deletion over addition.
- Shortest working diff that fixes the root cause.
