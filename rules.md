# Cursor AI Rules

Architecture:
- Use PHP OOP architecture
- Keep classes small and focused
- Separate services, post types, and frontend logic

Security:
- Sanitize all input
- Escape all output
- Use WordPress nonces
- Validate capabilities

Structure:
- includes/post-types
- includes/services
- includes/admin
- includes/frontend
- templates
- assets

Coding standards:
- Follow WordPress coding standards
- Prefix everything with mahl_

Workflow:
Always:
1. Propose implementation plan
2. Show files to modify
3. Show diff
4. Then generate code