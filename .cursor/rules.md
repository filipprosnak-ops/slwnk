# Cursor AI Rules

Architecture:
- Use PHP OOP architecture
- Keep classes small and focused
- Separate services, post types, admin modules, and frontend logic
- Treat MAHL Manager as a CMS-style WordPress module
- All core content must be created and managed in the WordPress admin using custom post types
- Frontend pages must be generated automatically from stored data
- Core functionality must not rely on Gutenberg blocks, shortcodes, or page builders
- Render frontend output through custom post type archive templates, custom post type single templates, and a plugin template loader system
- Core frontend pages should exist automatically, similar to WooCommerce product pages
- No manual page creation should be required for core functionality

Core frontend expectations:
- /seasons
- /teams
- /players
- /games
- /standings

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
